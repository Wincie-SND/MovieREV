<?php

namespace Tests\Unit;

use App\Services\TmdbMovie;
use App\Services\TmdbMovieDetail;
use Tests\TestCase;

class TmdbMovieDetailTest extends TestCase
{
    public function test_runtime_is_formatted_for_the_details_table(): void
    {
        $this->assertSame('2h 06m', $this->detailWith(runtime: 126)->runtimeLabel());
        $this->assertSame('0h 45m', $this->detailWith(runtime: 45)->runtimeLabel());

        $this->assertNull($this->detailWith()->runtimeLabel());
        $this->assertNull($this->detailWith(runtime: 0)->runtimeLabel());
    }

    public function test_a_backdrop_is_only_rendered_from_an_http_url(): void
    {
        foreach ([null, 'javascript:alert(1)', 'example.com/backdrop.jpg', 'https://x.test/a b.jpg'] as $unsafe) {
            $this->assertNull($this->detailWith(backdropUrl: $unsafe)->backdropSource(), "[{$unsafe}] must not be rendered");
        }

        $this->assertSame(
            'https://image.tmdb.org/t/p/w1280/dune.jpg',
            $this->detailWith(backdropUrl: 'https://image.tmdb.org/t/p/w1280/dune.jpg')->backdropSource(),
        );
    }

    public function test_a_detail_knows_whether_it_has_a_cast(): void
    {
        $this->assertFalse($this->detailWith()->hasCast());

        $this->assertTrue($this->detailWith(cast: [
            ['name' => 'Timothée Chalamet', 'character' => 'Paul Atreides', 'photo_url' => null],
        ])->hasCast());
    }

    /**
     * @param  array<int, array{name: string, character: string|null, photo_url: string|null}>  $cast
     */
    private function detailWith(?int $runtime = null, ?string $backdropUrl = null, array $cast = []): TmdbMovieDetail
    {
        return new TmdbMovieDetail(
            movie: new TmdbMovie(id: 693134, title: 'Dune: Part Two'),
            genres: [],
            similar: collect(),
            runtime: $runtime,
            backdrop_url: $backdropUrl,
            cast: $cast,
        );
    }
}
