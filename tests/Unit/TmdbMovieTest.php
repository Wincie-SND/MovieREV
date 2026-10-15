<?php

namespace Tests\Unit;

use App\Services\TmdbClient;
use App\Services\TmdbMovie;
use Tests\TestCase;

class TmdbMovieTest extends TestCase
{
    public function test_a_tmdb_score_is_normalised_to_five_stars(): void
    {
        $movie = TmdbMovie::fromTmdb([
            'id' => 693134,
            'title' => 'Dune: Part Two',
            'overview' => 'Paul Atreides unites with the Fremen.',
            'release_date' => '2024-02-27',
            'vote_average' => 8.2,
            'vote_count' => 12345,
        ], 'Science Fiction, Adventure', 'https://image.tmdb.org/t/p/w500/dune.jpg');

        $this->assertSame(693134, $movie->id);
        $this->assertSame('Dune: Part Two', $movie->title);
        $this->assertSame('Science Fiction, Adventure', $movie->genre);
        $this->assertSame(2024, $movie->release_year);
        $this->assertSame(12345, $movie->reviews_count);
        $this->assertSame(4.1, round($movie->average_rating, 2));
        $this->assertSame('https://image.tmdb.org/t/p/w500/dune.jpg', $movie->posterSource());
    }

    public function test_missing_tmdb_fields_degrade_to_placeholders(): void
    {
        $movie = TmdbMovie::fromTmdb([
            'id' => 1,
            'title' => '',
            'overview' => '',
            'release_date' => 'not-a-date',
            'vote_average' => 0,
            'vote_count' => 0,
        ]);

        $this->assertSame('Untitled', $movie->title);
        $this->assertNull($movie->description);
        $this->assertNull($movie->release_year);
        $this->assertNull($movie->posterSource());
    }

    public function test_a_poster_is_only_rendered_from_an_http_url(): void
    {
        $movie = TmdbMovie::fromTmdb(['id' => 1, 'title' => 'Rated Title']);

        foreach (['', '   ', 'javascript:alert(1)', 'data:image/png;base64,x', 'example.com/poster.jpg', 'poster with space.jpg', '/img/poster.jpg'] as $unsafe) {
            $this->assertNull((new TmdbMovie(id: 1, title: 'T', poster_url: $unsafe))->posterSource(), "[{$unsafe}] must not be rendered");
        }

        $this->assertSame('https://example.com/poster.jpg', (new TmdbMovie(id: 1, title: 'T', poster_url: 'https://example.com/poster.jpg'))->posterSource());
        $this->assertSame('http://example.com/poster.jpg', (new TmdbMovie(id: 1, title: 'T', poster_url: 'http://example.com/poster.jpg'))->posterSource());
        $this->assertNull($movie->posterSource());
    }

    public function test_image_urls_are_built_from_the_configured_base(): void
    {
        config()->set('services.tmdb.image_base_url', 'https://image.tmdb.org/t/p');

        $client = new TmdbClient;

        $this->assertSame('https://image.tmdb.org/t/p/w500/dune.jpg', $client->imageUrl('/dune.jpg'));
        $this->assertSame('https://image.tmdb.org/t/p/original/dune.jpg', $client->imageUrl('dune.jpg', 'original'));

        foreach ([null, '', '   ', '../../etc/passwd', 'dune poster.jpg', 'x<y.jpg'] as $unsafe) {
            $this->assertNull($client->imageUrl($unsafe));
        }
    }

    public function test_the_client_is_unconfigured_without_a_credential(): void
    {
        config()->set('services.tmdb.key', null);
        config()->set('services.tmdb.token', null);

        $client = new TmdbClient;

        $this->assertFalse($client->isConfigured());
        $this->assertTrue($client->popular()->isEmpty());

        config()->set('services.tmdb.token', 'read-access-token');

        $this->assertTrue($client->isConfigured());
    }
}
