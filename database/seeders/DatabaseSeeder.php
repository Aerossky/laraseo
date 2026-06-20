<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default accounts — CHANGE THESE CREDENTIALS before going live.
        // One per role so each permission level can be tried out immediately.
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => Hash::make('password'), 'role' => UserRole::Admin],
        );

        // Give the demo author a populated profile so the author box renders in
        // full. Only fills empty fields, so it never clobbers real edits.
        if (! $admin->bio) {
            $admin->update([
                'bio' => 'Founder and lead writer at laraseo — sharing practical, SEO-first Laravel tips.',
                'website' => 'https://example.com',
                'twitter' => 'https://x.com/example',
                'linkedin' => 'https://www.linkedin.com/in/example',
            ]);
        }

        User::firstOrCreate(
            ['email' => 'editor@example.com'],
            ['name' => 'Editor User', 'password' => Hash::make('password'), 'role' => UserRole::Editor],
        );

        User::firstOrCreate(
            ['email' => 'author@example.com'],
            ['name' => 'Author User', 'password' => Hash::make('password'), 'role' => UserRole::Author],
        );

        $this->call([
            SettingsSeeder::class,
            DemoContentSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
