<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_dashboard_lists_movies_from_tmdb(): void
    {
        $this->fakeTmdb([
            $this->tmdbMovie(693134, 'Dune: Part Two', '2024-02-27', 8.2, 12345, '/dune.jpg', [878, 12]),
            $this->tmdbMovie(603, 'The Matrix', '1999-03-31', 8.7, 24000, '/matrix.jpg', [28]),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Dune: Part Two')
            ->assertSee('The Matrix')
            ->assertSee('Popular Films')
            ->assertSee('Science Fiction, Adventure')
            // TMDB scores out of ten; the five-star UI halves it.
            ->assertSee('4.1')
            ->assertSee('https://image.tmdb.org/t/p/w500/dune.jpg');
    }

    public function test_the_search_box_queries_tmdb_instead_of_the_database(): void
    {
        $this->fakeTmdb([
            $this->tmdbMovie(693134, 'Dune: Part Two', '2024-02-27', 8.2, 12345, '/dune.jpg', []),
        ]);

        $this->get('/?q=dune')
            ->assertOk()
            ->assertSee('Dune: Part Two')
            ->assertSee('Showing catalog matches for');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/search/movie')
            && str_contains($request->url(), 'query=dune'));

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), '/movie/popular'));
    }

    public function test_dashboard_renders_placeholders_without_a_tmdb_credential(): void
    {
        config()->set('services.tmdb.key', null);

        Http::fake();
        Http::preventStrayRequests();

        $this->get('/')
            ->assertOk()
            ->assertSee('Placeholder title')
            ->assertSee('Popular Films');
    }

    public function test_dashboard_survives_a_tmdb_failure(): void
    {
        Http::fake([
            '*/movie/popular*' => Http::response(['status_message' => 'Invalid API key'], 401),
            '*/genre/movie/list*' => Http::response(['genres' => []]),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Placeholder title')
            ->assertSee('Popular Reviews This Week');
    }

    /**
     * The home page must not depend on movie rows. When the database cannot
     * answer (a refused connection, or a fresh checkout without the `reviews`
     * table) the community sections degrade instead of 500ing.
     */
    public function test_dashboard_renders_when_the_review_tables_are_unavailable(): void
    {
        Schema::dropIfExists('reviews');

        $this->fakeTmdb([
            $this->tmdbMovie(693134, 'Dune: Part Two', '2024-02-27', 8.2, 12345, '/dune.jpg', []),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Dune: Part Two')
            ->assertSee('Popular Reviews This Week');
    }

    public function test_recently_released_films_come_from_tmdb(): void
    {
        $this->fakeTmdb(
            [$this->tmdbMovie(693134, 'Dune: Part Two', '2024-02-27', 8.2, 12345, '/dune.jpg', [])],
            [$this->tmdbMovie(1022789, 'Inside Out 2', '2024-06-11', 7.6, 8000, '/inside-out-2.jpg', [16])],
        );

        $this->get('/')
            ->assertOk()
            ->assertSee('Recently Released')
            ->assertSee('Inside Out 2')
            ->assertSee('https://image.tmdb.org/t/p/w500/inside-out-2.jpg')
            // Both grids are filled from TMDB, so nothing falls back.
            ->assertDontSee('Title placeholder');
    }

    public function test_movie_cards_link_to_the_detail_page(): void
    {
        $this->fakeTmdb([$this->tmdbMovie(693134, 'Dune: Part Two', '2024-02-27', 8.2, 12345, '/dune.jpg', [])]);

        $this->get('/')
            ->assertOk()
            ->assertSee(route('movies.show', 693134));
    }

    public function test_review_search_matches_the_movie_title(): void
    {
        $first = $this->createUser('Reviewer One', 'one@example.com', 'reviewer_one');
        $second = $this->createUser('Reviewer Two', 'two@example.com', 'reviewer_two');

        Review::create(['user_id' => $first->id, 'tmdb_id' => 693134, 'movie_title' => 'Dune: Part Two', 'rating' => 5, 'body' => 'Superb.']);
        Review::create(['user_id' => $second->id, 'tmdb_id' => 603, 'movie_title' => 'The Matrix', 'rating' => 4, 'body' => 'Still holds up.']);

        $this->fakeTmdb([]);

        $this->get('/?q=matrix')
            ->assertOk()
            ->assertSee('The Matrix')
            ->assertDontSee('Superb.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $movies
     * @param  array<int, array<string, mixed>>  $nowPlaying
     */
    private function fakeTmdb(array $movies, array $nowPlaying = []): void
    {
        Http::fake([
            '*/movie/popular*' => Http::response(['results' => $movies]),
            '*/movie/now_playing*' => Http::response(['results' => $nowPlaying]),
            '*/search/movie*' => Http::response(['results' => $movies]),
            '*/genre/movie/list*' => Http::response(['genres' => [
                ['id' => 878, 'name' => 'Science Fiction'],
                ['id' => 12, 'name' => 'Adventure'],
                ['id' => 28, 'name' => 'Action'],
            ]]),
        ]);
    }

    /**
     * @param  array<int, int>  $genreIds
     * @return array<string, mixed>
     */
    private function tmdbMovie(int $id, string $title, string $releaseDate, float $voteAverage, int $voteCount, string $posterPath, array $genreIds): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'overview' => 'A movie about things happening.',
            'release_date' => $releaseDate,
            'vote_average' => $voteAverage,
            'vote_count' => $voteCount,
            'poster_path' => $posterPath,
            'genre_ids' => $genreIds,
        ];
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
