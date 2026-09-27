<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Guarded so `php artisan db:seed` can be run repeatedly without
        // tripping the unique constraint on the email column.
        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Demo data for Dagupan City. Every seeder matches records by name, so
        // running this again updates instead of duplicating.
        $this->call([
            AdminUserSeeder::class,
            HotelSeeder::class,
            RestaurantSeeder::class,
            TouristSpotSeeder::class,
            MallSeeder::class,
        ]);
    }
}

