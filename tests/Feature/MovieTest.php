<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MovieTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('movies');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

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

        Schema::create('movies', function ($table): void {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->smallInteger('release_year')->nullable();
            $table->string('genre', 50)->nullable();
            $table->string('poster_url', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('reviews', function ($table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('movie_id');
            $table->tinyInteger('rating');
            $table->text('body')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'movie_id']);
            $table->foreign('movie_id')->references('id')->on('movies')->cascadeOnDelete();
        });
    }

    public function test_dashboard_shows_admin_entered_movie_and_empty_placeholders(): void
    {
        $movie = Movie::create([
            'title' => 'Placeholder Verified',
            'description' => 'An administrator-entered catalog entry.',
            'release_year' => 2026,
            'genre' => 'Drama',
            'poster_url' => null,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Bebas+Neue')
            ->assertSee($movie->title)
            ->assertSee('Title placeholder')
            ->assertSee('Popular Films');
    }

    public function test_dashboard_filters_movies_by_search(): void
    {
        Movie::create(['title' => 'Dune Test', 'genre' => 'Sci-Fi']);
        Movie::create(['title' => 'Unrelated Entry', 'genre' => 'Drama']);

        $this->get('/?q=dune')
            ->assertOk()
            ->assertSee('Dune Test')
            ->assertDontSee('Unrelated Entry');
    }

    public function test_dashboard_works_when_catalog_tables_are_absent(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('movies');

        $this->get('/')
            ->assertOk()
            ->assertSee('Placeholder title')
            ->assertSee('Popular Reviews This Week');
    }

    public function test_admin_can_add_movie(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => 'Admin Added Title',
            'release_year' => 2025,
            'genre' => 'Western',
            'description' => 'Entered through the admin form.',
            'poster_url' => 'https://example.com/poster.jpg',
        ]);

        $response->assertRedirect(route('admin.movies.index'));
        $this->assertDatabaseHas('movies', [
            'title' => 'Admin Added Title',
            'release_year' => 2025,
            'poster_url' => 'https://example.com/poster.jpg',
        ]);
        $this->get('/')->assertSee('Admin Added Title');
    }

    public function test_guest_is_redirected_from_movie_management(): void
    {
        $this->get(route('admin.movies.index'))->assertRedirect('/login');
        $this->post(route('admin.movies.store'), ['title' => 'Nope'])->assertRedirect('/login');
    }

    public function test_non_admin_is_forbidden_from_movie_management(): void
    {
        $user = $this->createUser('user');

        $this->actingAs($user)->get(route('admin.movies.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.movies.store'), [
            'title' => 'Nope',
        ])->assertForbidden();
    }

    public function test_movie_input_is_validated(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->post(route('admin.movies.store'), [
            'title' => '',
            'release_year' => 1800,
            'poster_url' => str_repeat('x', 256),
        ]);

        $response->assertSessionHasErrors(['title', 'release_year', 'poster_url']);
        $this->assertDatabaseCount('movies', 0);
    }

    public function test_admin_can_update_and_delete_movie(): void
    {
        $admin = $this->createUser('admin');
        $movie = Movie::create(['title' => 'Before']);

        $this->actingAs($admin)->patch(route('admin.movies.update', $movie), [
            'title' => 'After',
        ])->assertRedirect(route('admin.movies.index'));
        $this->assertDatabaseHas('movies', ['id' => $movie->id, 'title' => 'After']);

        $this->actingAs($admin)->delete(route('admin.movies.destroy', $movie))
            ->assertRedirect(route('admin.movies.index'));
        $this->assertDatabaseMissing('movies', ['id' => $movie->id]);
    }

    public function test_dashboard_includes_review_average(): void
    {
        $admin = $this->createUser('admin');
        $other = User::create([
            'name' => 'Reviewer',
            'email' => 'reviewer@example.com',
            'password' => 'password',
            'username' => 'reviewer',
            'usertype' => 'user',
        ]);
        $movie = Movie::create(['title' => 'Rated Title']);

        Review::create(['user_id' => $admin->id, 'movie_id' => $movie->id, 'rating' => 5, 'body' => 'Excellent.']);
        Review::create(['user_id' => $other->id, 'movie_id' => $movie->id, 'rating' => 4, 'body' => 'Very good.']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rated Title')
            ->assertSee('4.5')
            ->assertSee('2 reviews');
    }

    public function test_poster_source_permits_only_http_or_site_relative_values(): void
    {
        $movie = new Movie;

        $movie->poster_url = 'https://example.com/poster.jpg';
        $this->assertSame('https://example.com/poster.jpg', $movie->posterSource());

        $movie->poster_url = '/img/poster.jpg';
        $this->assertSame('/img/poster.jpg', $movie->posterSource());

        foreach (['', '   ', 'javascript:alert(1)', 'data:image/png;base64,x', 'example.com/poster.jpg', 'poster with space.jpg'] as $unsafe) {
            $movie->poster_url = $unsafe;
            $this->assertNull($movie->posterSource());
        }
    }

    private function createUser(string $usertype): User
    {
        return User::create([
            'name' => ucfirst($usertype).' User',
            'email' => $usertype.'@example.com',
            'password' => 'password',
            'username' => $usertype.'_user',
            'usertype' => $usertype,
        ]);
    }
}
