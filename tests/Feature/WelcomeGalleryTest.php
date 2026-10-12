<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ang "A city full of stories" gallery sa landing page ay nagpapakita ng
 * PAREHONG katalogo ng Step 03 "Pick your places" picker: parehong kategorya
 * (restaurant / mall / beach / tourist), larawan na may category fallback,
 * presyo kada araw, at chips na kapareho ng picker filter. Kapag walang
 * naka-seed na lugar, curated na gallery ang fallback — hindi blangko ang
 * seksyon.
 */
class WelcomeGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function makeLocation(string $name, string $category, float $cost, ?string $image = null): Location
    {
        return Location::create([
            'name' => $name,
            'location' => 'Dagupan City',
            'latitude' => 16.0450,
            'longitude' => 120.3400,
            'rating' => 8.5,
            'category' => $category,
            'icon' => '📍',
            'image_url' => $image,
            'budgetPerDay' => $cost,
            'description' => 'A test place.',
        ]);
    }

    public function test_the_story_gallery_shows_the_step_03_catalogue(): void
    {
        $this->makeLocation('SM Center Dagupan', Location::CATEGORY_MALL, 300);
        $this->makeLocation('Tondaligan Blue Beach', Location::CATEGORY_BEACH, 100);
        $this->makeLocation('Pigar-Pigar', Location::CATEGORY_RESTAURANT, 150);
        $this->makeLocation('Dagupan City Museum', Location::CATEGORY_TOURIST, 50);

        $response = $this->get('/');

        $response->assertOk();

        // Chips: parehong key at label ng Step 03 filter, may counts.
        // Walang icon/emoji — malinis na text label lang (tinanggal na ang
        // emoji sa gallery; ang picker ay may sariling emoji pa rin).
        $response->assertSee('class="gallery-filters"', false);
        $response->assertSee('aria-label="Filter gallery by category"', false);
        $response->assertSee('data-gallery-filter="all"', false);
        $response->assertSee('data-gallery-filter="restaurant"', false);
        $response->assertSee('data-gallery-filter="mall"', false);
        $response->assertSee('data-gallery-filter="beach"', false);
        $response->assertSee('data-gallery-filter="tourist"', false);
        $response->assertSee('Restaurants', false);
        $response->assertSee('Tourist spots', false);

        // Bawat lugar sa katalogo ay nasa gallery.
        $response->assertSee('SM Center Dagupan', false);
        $response->assertSee('Tondaligan Blue Beach', false);
        $response->assertSee('Pigar-Pigar', false);
        $response->assertSee('Dagupan City Museum', false);

        // Presyo kada araw, gaya ng Step 03 place card.
        $response->assertSee('₱100/day', false);

        // Bawat card ay may kategorya ng lugar...
        $this->assertSame(
            4,
            substr_count($response->getContent(), 'data-gallery-category='),
            'Every gallery card must carry the place\'s Step 03 category.'
        );
        $response->assertSee('class="gallery-cat"', false);

        // ...at walang basag na <img>: ang walang sariling larawan ay
        // tumatanggap ng category default (tulad ng picker).
        $response->assertSee('/images/places/mall-default.jpg', false);
        $response->assertSee('/images/places/beach-default.jpg', false);

        // Magnify (⤢) → map modal: may coords sa payload, may Leaflet, at may
        // button bawat catalogue card na may tunay na lat/lon.
        $response->assertSee('id="gallery-map-modal"', false);
        $response->assertSee('data-gallery-zoom=', false);
        $response->assertSee('function openGalleryMap(', false);
        $response->assertSee('function closeGalleryMap()', false);
        $response->assertSee('leaflet@1.9.4', false);
        $response->assertSee('"latitude":16.045', false);
        $response->assertDontSee('Open in Google Maps', false);
        $response->assertDontSee('gallery-map-link', false);
        $this->assertSame(
            4,
            substr_count($response->getContent(), 'data-gallery-zoom="'),
            'Every catalogue card with coordinates must expose a magnify button.'
        );
    }

    public function test_the_gallery_falls_back_to_the_curated_cards_when_the_catalogue_is_empty(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // Walang naka-seed: nandoon pa rin ang curated na 12-card gallery,
        // at may sariling chips ito (festival/streets ay curated-only —
        // wala ang mga iyon sa katalogo-chips sa itaas).
        $response->assertSee('data-gallery-filter="festival"', false);
        $response->assertSee('data-gallery-filter="streets"', false);
        $response->assertSee('A city full of stories', false);
        $this->assertSame(
            12,
            substr_count($response->getContent(), 'data-gallery-category='),
            'The curated fallback must still render all 12 cards.'
        );

        // Walang coordinates ang curated cards → walang magnify button, at
        // hindi rin kinukuha ang Leaflet (conditional include sa head). Ang
        // modal shell ay naka-render pa rin sa parehong path.
        $response->assertSee('id="gallery-map-modal"', false);
        $response->assertDontSee('data-gallery-zoom="', false);
        $response->assertDontSee('leaflet@1.9.4', false);
    }
}
