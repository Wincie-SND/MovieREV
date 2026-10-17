<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MoviePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('review_likes');
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

        Schema::create('review_likes', function ($table): void {
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['review_id', 'user_id']);
        });

        config()->set('services.tmdb.key', 'test-key');
        config()->set('services.tmdb.token', null);
    }

    public function test_the_page_renders_everything_tmdb_supplies(): void
    {
        $this->fakeDetail();

        $this->get('/movies/693134')
            ->assertOk()
            ->assertSee('Dune: Part Two')
            ->assertSee('Long live the fighters.')
            // The design's metadata line and details table.
            ->assertSee('2h 46m')
            ->assertSee('PG-13')
            ->assertSee('Action, Adventure')
            ->assertSee('Denis Villeneuve')
            // Billing block.
            ->assertSee('Timothée Chalamet')
            ->assertSee('Paul Atreides')
            // Section furniture.
            ->assertSee('Synopsis')
            ->assertSee('Cast &amp; Crew', false)
            ->assertSee('Similar Films')
            // A full-size backdrop, not the poster.
            ->assertSee('https://image.tmdb.org/t/p/w1280/dune-backdrop.jpg')
            ->assertSee('https://image.tmdb.org/t/p/w500/dune.jpg')
            ->assertSee('No reviews yet');
    }

    public function test_a_movie_page_lists_its_member_reviews(): void
    {
        $user = $this->createUser('Reviewer One', 'one@example.com', 'reviewer_one');

        Review::create([
            'user_id' => $user->id,
            'tmdb_id' => 693134,
            'movie_title' => 'Dune: Part Two',
            'poster_url' => 'https://image.tmdb.org/t/p/w500/dune.jpg',
            'rating' => 5,
            'body' => 'Superb.',
        ]);

        $this->fakeDetail();

        $this->get('/movies/693134')
            ->assertOk()
            ->assertSee('reviewer_one')
            ->assertSee('Superb.')
            ->assertDontSee('No reviews yet');
    }

    /**
     * Like the dashboard, the page must survive MySQL being down: only the
     * reviews section reads the database.
     */
    public function test_the_page_renders_without_the_reviews_table(): void
    {
        Schema::dropIfExists('reviews');

        $this->fakeDetail();

        $this->get('/movies/693134')
            ->assertOk()
            ->assertSee('Dune: Part Two')
            ->assertSee('No reviews yet');
    }

    public function test_an_id_tmdb_does_not_know_is_a_404(): void
    {
        Http::fake([
            '*/movie/999999*' => Http::response(['status_message' => 'The resource you requested could not be found.'], 404),
        ]);

        $this->get('/movies/999999')->assertNotFound();
    }

    public function test_a_tmdb_outage_is_a_503(): void
    {
        Http::fake([
            '*/movie/693134*' => Http::response(['status_message' => 'Internal error'], 500),
        ]);

        $this->get('/movies/693134')->assertStatus(503);
    }

    public function test_an_unconfigured_catalog_is_a_404(): void
    {
        config()->set('services.tmdb.key', null);
        config()->set('services.tmdb.token', null);

        Http::preventStrayRequests();

        $this->get('/movies/693134')->assertNotFound();
    }

    public function test_a_non_numeric_id_is_a_404(): void
    {
        Http::preventStrayRequests();

        $this->get('/movies/dune')->assertNotFound();
    }

    /**
     * One `/movie/{id}?append_to_response=...` response covering every field
     * the page reads: details, credits, US certification and similar films.
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
                        ['name' => 'Hans Zimmer', 'job' => 'Original Music Composer'],
                    ],
                ],
                'release_dates' => [
                    'results' => [
                        ['iso_3166_1' => 'GB', 'release_dates' => [['certification' => '12A']]],
                        ['iso_3166_1' => 'US', 'release_dates' => [['certification' => 'PG-13']]],
                    ],
                ],
                'similar' => [
                    'results' => [
                        [
                            'id' => 603,
                            'title' => 'The Matrix',
                            'overview' => 'A hacker learns the truth.',
                            'release_date' => '1999-03-31',
                            'vote_average' => 8.7,
                            'vote_count' => 24000,
                            'poster_path' => '/matrix.jpg',
                            'genre_ids' => [878],
                        ],
                    ],
                ],
            ]),
            '*/genre/movie/list*' => Http::response(['genres' => [
                ['id' => 878, 'name' => 'Science Fiction'],
            ]]),
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
