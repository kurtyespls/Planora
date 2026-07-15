<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user - force role to admin
        $admin = User::where('email', 'planora@planora.com')->first();
        if ($admin) {
            $admin->update(['role' => 'admin']);
        } else {
            User::create([
                'name' => 'Admin User',
                'email' => 'planora@planora.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]);
        }

        // Optional: Create a regular user for testing
        $user = User::where('email', 'user@planora.com')->first();
        if (!$user) {
            User::create([
                'name' => 'Test User',
                'email' => 'user@planora.com',
                'password' => Hash::make('user123'),
                'role' => 'user',
            ]);
        }
    }
}