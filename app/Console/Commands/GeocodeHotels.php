<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GeocodeHotels extends Command
{
    protected $signature = 'planora:geocode-hotels
                            {--force : Re-geocode hotels that already have coordinates}';

    protected $description = 'Geocode all hotels using OpenStreetMap Nominatim API to populate lat/lon/address fields';

    /**
     * Nominatim requires a valid User-Agent and has a 1 req/sec rate limit.
     * We'll respect both.
     */
    private const NOMINATIM_BASE = 'https://nominatim.openstreetmap.org/search';

    public function handle(): int
    {
        $query = Hotel::query();

        if (!$this->option('force')) {
            $query->whereNull('lat')->orWhereNull('lon');
        }

        $hotels = $query->get();

        if ($hotels->isEmpty()) {
            $this->info('All hotels already have coordinates. Use --force to re-geocode.');
            return Command::SUCCESS;
        }

        $this->info("Geocoding {$hotels->count()} hotel(s)...");
        $bar = $this->output->createProgressBar($hotels->count());
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($hotels as $hotel) {
            $result = $this->geocode($hotel->name);

            if ($result) {
                $hotel->lat = $result['lat'];
                $hotel->lon = $result['lon'];
                $hotel->address = $result['address'] ?? $hotel->address;
                $hotel->save();
                $successCount++;
                $this->line("  ✓ {$hotel->name} → ({$result['lat']}, {$result['lon']})");
            } else {
                // Fallback: try with "Dagupan City" suffix
                $result = $this->geocode($hotel->name . ', Dagupan City, Pangasinan');
                if ($result) {
                    $hotel->lat = $result['lat'];
                    $hotel->lon = $result['lon'];
                    $hotel->address = $result['address'] ?? $hotel->address;
                    $hotel->save();
                    $successCount++;
                    $this->line("  ✓ {$hotel->name} (with Dagupan suffix) → ({$result['lat']}, {$result['lon']})");
                } else {
                    $this->warn("  ✗ Could not geocode: {$hotel->name}");
                    $failCount++;
                }
            }

            $bar->advance();

            // Nominatim rate limit: 1 request per second
            if ($bar->getProgress() < $hotels->count()) {
                usleep(1_100_000); // 1.1 seconds
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. {$successCount} geocoded, {$failCount} failed.");

        // Clear hotels cache so frontend picks up coords
        CacheService::clearHotelsCache();

        return Command::SUCCESS;
    }

    /**
     * Geocode a single query string via Nominatim.
     */
    private function geocode(string $query): ?array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'User-Agent' => 'PlanoraItineraryApp/1.0 (dagupan-travel-planner)',
                    'Accept-Language' => 'en',
                ])->get(self::NOMINATIM_BASE, [
                    'q' => $query . ', Dagupan City, Pangasinan, Philippines',
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            if (empty($data) || !isset($data[0]['lat'])) {
                return null;
            }

            $result = $data[0];
            $addressParts = [];
            $addr = $result['address'] ?? [];
            if (!empty($addr['road'])) $addressParts[] = $addr['road'];
            if (!empty($addr['house_number'])) $addressParts[] = $addr['house_number'];
            if (!empty($addr['suburb'])) $addressParts[] = $addr['suburb'];
            if (!empty($addr['city'])) $addressParts[] = $addr['city'];
            if (!empty($addr['state'])) $addressParts[] = $addr['state'];

            return [
                'lat' => (float) $result['lat'],
                'lon' => (float) $result['lon'],
                'address' => !empty($addressParts) ? implode(', ', $addressParts) : ($result['display_name'] ?? null),
            ];
        } catch (\Exception $e) {
            $this->warn("  [HTTP Error] {$e->getMessage()}");
            return null;
        }
    }
}