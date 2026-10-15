<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * MovieREV's own review system: submission, ownership, averages and escaping.
 *
 * TMDB only ever supplies movie information here, so a payload that *does*
 * carry reviews is used throughout to prove none of it reaches the page.
 */
class ReviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('reviews');
        Schema::dropIfExists('users');

        Schema::create('users', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('username', 50)->unique();
            $table->enum('usertype', ['admin', 'user'])->default('user');
            $table->timestamps();
        });

        Schema::create('reviews', function ($table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('tmdb_id');
            $table->string('movie_title', 150);
            $table->string('poster_url', 255)->nullable();
            $table->tinyInteger('rating');
            $table->string('title', 120)->nullable();
            $table->text('body')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['user_id', 'tmdb_id']);
        });

        config()->set('services.tmdb.key', 'test-key');
        config()->set('services.tmdb.token', null);
    }

    public function test_an_empty_review_list_shows_the_empty_state_and_never_tmdb_reviews(): void
    {
        $this->fakeDetail();

        $this->get('/movies/693134')
            ->assertOk()
            ->assertSee('No reviews yet. Be the first to review')
            ->assertSee('No MovieREV community rating yet')
            // Guests are pointed at the auth pages instead of a form.
            ->assertSee('Want to review this movie')
            ->assertSee(route('login'))
            // Even though the TMDB payload carried reviews, none are shown.
            ->assertDontSee('External Reviewer')
            ->assertDontSee('A review written outside MovieREV.');

        // TMDB's review endpoint is never the fallback for an empty list.
        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), '/movie/693134/reviews'));
    }

    public function test_a_movie_shows_only_its_own_members_reviews(): void
    {
        $ada = $this->createUser('Ada', 'ada@example.com', 'ada');
        $grace = $this->createUser('Grace', 'grace@example.com', 'grace');

        $this->review($ada, 693134, 'Dune: Part Two', 5, 'Loved the sandworms.');
        $this->review($grace, 603, 'The Matrix', 4, 'Still holds up.');

        $this->fakeDetail();

        $this->get('/movies/693134')
            ->assertOk()
            ->assertSee('Loved the sandworms.')
            ->assertSee('ada')
            ->assertDontSee('Still holds up.')
            ->assertDontSee('No reviews yet');
    }

    public function test_a_guest_cannot_submit_a_review(): void
    {
        Http::preventStrayRequests();

        $this->post('/movies/693134/reviews', ['rating' => 5, 'body' => 'A perfectly good review.'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_a_member_can_submit_a_review(): void
    {
        $user = $this->createUser('Ada', 'ada@example.com', 'ada');
        $this->fakeDetail();

        $this->actingAs($user)
            ->post('/movies/693134/reviews', [
                'rating' => 4,
                'title' => 'Spicey goodness',
                'body' => 'A worthy sequel with real heft.',
            ])
            ->assertRedirect(route('movies.show', 693134).'#reviews')
            ->assertSessionHas('status', 'Thanks! Your review has been posted.');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'tmdb_id' => 693134,
            'movie_title' => 'Dune: Part Two',
            'rating' => 4,
            'title' => 'Spicey goodness',
            'body' => 'A worthy sequel with real heft.',
        ]);

        $this->actingAs($user)->get('/movies/693134')
            ->assertOk()
            ->assertSee('Spicey goodness')
            ->assertSee('A worthy sequel with real heft.')
            ->assertSee('ada')
            ->assertDontSee('No reviews yet');
    }

    public function test_invalid_reviews_are_rejected(): void
    {
        $user = $this->createUser('Ada', 'ada@example.com', 'ada');

        Http::preventStrayRequests();

        // Rating out of range and no written review.
        $this->actingAs($user)->from('/movies/693134')
            ->post('/movies/693134/reviews', ['rating' => 9, 'body' => ''])
            ->assertRedirect('/movies/693134')
            ->assertSessionHasErrors(['rating', 'body']);

        // Over-long optional title.
        $this->actingAs($user)->from('/movies/693134')
            ->post('/movies/693134/reviews', [
                'rating' => 5,
                'title' => str_repeat('a', 121),
                'body' => 'A perfectly good review.',
            ])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_a_member_can_edit_their_own_review(): void
    {
        $user = $this->createUser('Ada', 'ada@example.com', 'ada');
        $review = $this->review($user, 693134, 'Dune: Part Two', 3, 'It was merely fine.');

        $this->actingAs($user)
            ->put("/reviews/{$review->id}", [
                'rating' => 5,
                'title' => 'Second thoughts',
                'body' => 'It grew on me enormously.',
            ])
            ->assertRedirect(route('movies.show', 693134).'#reviews')
            ->assertSessionHas('status', 'Your review has been updated.');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'title' => 'Second thoughts',
            'body' => 'It grew on me enormously.',
        ]);
    }

    public function test_a_member_sees_their_own_review_controls_and_prefilled_edit_form(): void
    {
        $user = $this->createUser('Ada', 'ada@example.com', 'ada');
        $review = $this->review($user, 693134, 'Dune: Part Two', 4, 'My considered take.', 'Worth the trip');
        $this->fakeDetail();

        // Their own review is flagged, with the management controls.
        $this->actingAs($user)->get('/movies/693134')
            ->assertOk()
            ->assertSee('You have reviewed this film')
            ->assertSee('Delete review')
            ->assertSee('My considered take.')
            ->assertSee('Worth the trip')
            ->assertDontSee('No reviews yet');

        // `?edit={id}` refills the form instead of offering a blank one.
        $this->actingAs($user)->get("/movies/693134?edit={$review->id}")
            ->assertOk()
            ->assertSee('Edit your review')
            ->assertSee(route('reviews.update', $review))
            ->assertSee('Worth the trip')
            ->assertSee('My considered take.');
    }

    public function test_a_member_cannot_open_another_members_edit_form(): void
    {
        $owner = $this->createUser('Owner', 'owner@example.com', 'owner');
        $intruder = $this->createUser('Intruder', 'intruder@example.com', 'intruder');
        $review = $this->review($owner, 693134, 'Dune: Part Two', 5, 'Owner only review.');
        $this->fakeDetail();

        $this->actingAs($intruder)->get("/movies/693134?edit={$review->id}")
            ->assertOk()
            ->assertSee('Write a review')
            ->assertDontSee('Edit your review')
            ->assertDontSee(route('reviews.update', $review));
    }

    public function test_a_member_cannot_edit_another_members_review(): void
    {
        $owner = $this->createUser('Owner', 'owner@example.com', 'owner');
        $intruder = $this->createUser('Intruder', 'intruder@example.com', 'intruder');
        $review = $this->review($owner, 693134, 'Dune: Part Two', 3, 'My own review.');

        $this->actingAs($intruder)
            ->put("/reviews/{$review->id}", ['rating' => 1, 'body' => 'Hijacked review body.'])
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'body' => 'My own review.',
        ]);
    }

    public function test_a_member_can_delete_their_own_review(): void
    {
        $user = $this->createUser('Ada', 'ada@example.com', 'ada');
        $review = $this->review($user, 693134, 'Dune: Part Two', 4, 'Good enough to delete.');

        $this->actingAs($user)
            ->delete("/reviews/{$review->id}")
            ->assertRedirect(route('movies.show', 693134).'#reviews')
            ->assertSessionHas('status', 'Your review has been deleted.');

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_a_member_cannot_delete_another_members_review(): void
    {
        $owner = $this->createUser('Owner', 'owner@example.com', 'owner');
        $intruder = $this->createUser('Intruder', 'intruder@example.com', 'intruder');
        $review = $this->review($owner, 693134, 'Dune: Part Two', 3, 'My own review.');

        $this->actingAs($intruder)
            ->delete("/reviews/{$review->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_a_member_cannot_review_the_same_movie_twice(): void
    {
        $user = $this->createUser('Ada', 'ada@example.com', 'ada');
        $this->review($user, 693134, 'Dune: Part Two', 5, 'First take.');

        Http::preventStrayRequests();

        $this->actingAs($user)->from('/movies/693134')
            ->post('/movies/693134/reviews', ['rating' => 2, 'body' => 'A second attempt at the same film.'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'tmdb_id' => 693134,
            'rating' => 5,
        ]);
    }

    public function test_the_community_average_and_count_track_create_edit_and_delete(): void
    {
        $ada = $this->createUser('Ada', 'ada@example.com', 'ada');
        $grace = $this->createUser('Grace', 'grace@example.com', 'grace');
        $alan = $this->createUser('Alan', 'alan@example.com', 'alan');

        $this->fakeDetail();

        $this->review($ada, 693134, 'Dune: Part Two', 5, 'Five from Ada.');
        $graceReview = $this->review($grace, 693134, 'Dune: Part Two', 4, 'Four from Grace.');

        $this->get('/movies/693134')->assertOk()->assertSee('4.5')->assertSee('2 MovieREV reviews');

        // The requirement's own example: 5, 4 and 3 average to 4.0.
        $alanReview = $this->review($alan, 693134, 'Dune: Part Two', 3, 'Three from Alan.');
        $this->get('/movies/693134')->assertOk()->assertSee('4.0')->assertSee('3 MovieREV reviews');

        // Editing Alan's 3 into a 5 lifts the average to 4.7.
        $this->actingAs($alan)->put("/reviews/{$alanReview->id}", ['rating' => 5, 'body' => 'Upgraded after a rewatch.']);
        $this->get('/movies/693134')->assertOk()->assertSee('4.7')->assertSee('3 MovieREV reviews');

        // Deleting Grace's 4 leaves 5 and 5 — a perfect 5.0 from two reviews.
        $this->actingAs($grace)->delete("/reviews/{$graceReview->id}");
        $this->get('/movies/693134')->assertOk()->assertSee('5.0')->assertSee('2 MovieREV reviews');
    }

    public function test_reviews_persist_across_sessions(): void
    {
        $user = $this->createUser('Ada', 'ada@example.com', 'ada');
        $this->review($user, 693134, 'Dune: Part Two', 5, 'This review should survive a logout.');
        $this->fakeDetail();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $this->actingAs($user)->get('/movies/693134')
            ->assertOk()
            ->assertSee('This review should survive a logout.')
            ->assertDontSee('No reviews yet');
    }

    public function test_movie_information_still_comes_from_tmdb(): void
    {
        $this->fakeDetail();

        $this->get('/movies/693134')
            ->assertOk()
            ->assertSee('Dune: Part Two')
            ->assertSee('Timothée Chalamet')
            ->assertSee('Denis Villeneuve')
            ->assertSee('https://image.tmdb.org/t/p/w500/dune.jpg')
            ->assertSee('https://image.tmdb.org/t/p/w1280/dune-backdrop.jpg')
            // The TMDB score stays, clearly labelled against the community one.
            ->assertSee('TMDB')
            ->assertSee('MovieREV Community Reviews');
    }

    public function test_review_content_and_titles_are_escaped(): void
    {
        $user = $this->createUser('Ada', 'ada@example.com', 'ada');
        $payload = '<script>alert("xss")</script> plus some words';

        $this->review($user, 693134, 'Dune: Part Two', 5, $payload, '<img src=x onerror=alert(1)>');
        $this->fakeDetail();

        $this->get('/movies/693134')
            ->assertOk()
            ->assertDontSee($payload, false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;script&gt;alert', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
    }

    /**
     * One `/movie/{id}?append_to_response=...` response. It deliberately
     * carries a `reviews` block to prove MovieREV ignores it.
     */
    private function fakeDetail(): void
    {
        Http::fake([
            '*/movie/693134*' => Http::response([
                'id' => 693134,
                'title' => 'Dune: Part Two',
                'tagline' => 'Long live the fighters.',
                'overview' => 'Paul Atreides unites with the Fremen.',
                'release_date' => '2024-02-27',
                'runtime' => 166,
                'vote_average' => 8.2,
                'vote_count' => 12345,
                'poster_path' => '/dune.jpg',
                'backdrop_path' => '/dune-backdrop.jpg',
                'genres' => [
                    ['id' => 28, 'name' => 'Action'],
                    ['id' => 12, 'name' => 'Adventure'],
                ],
                'credits' => [
                    'cast' => [
                        ['name' => 'Timothée Chalamet', 'character' => 'Paul Atreides', 'profile_path' => '/timothee.jpg'],
                    ],
                    'crew' => [
                        ['name' => 'Denis Villeneuve', 'job' => 'Director'],
                    ],
                ],
                'release_dates' => [
                    'results' => [
                        ['iso_3166_1' => 'US', 'release_dates' => [['certification' => 'PG-13']]],
                    ],
                ],
                'similar' => ['results' => []],
                // Never requested, never rendered — present only as a guard.
                'reviews' => [
                    'results' => [
                        [
                            'author' => 'External Reviewer',
                            'content' => 'A review written outside MovieREV.',
                        ],
                    ],
                ],
            ]),
            '*/genre/movie/list*' => Http::response(['genres' => [
                ['id' => 878, 'name' => 'Science Fiction'],
            ]]),
        ]);
    }

    private function review(User $user, int $tmdbId, string $movieTitle, int $rating, string $body, ?string $reviewTitle = null): Review
    {
        return Review::create([
            'user_id' => $user->id,
            'tmdb_id' => $tmdbId,
            'movie_title' => $movieTitle,
            'poster_url' => 'https://image.tmdb.org/t/p/w500/dune.jpg',
            'rating' => $rating,
            'title' => $reviewTitle,
            'body' => $body,
        ]);
    }

    private function createUser(string $name, string $email, string $username): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'username' => $username,
            'usertype' => 'user',
        ]);
    }
}
