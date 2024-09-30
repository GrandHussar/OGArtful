<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the individual seeders
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            SiteSettingSeeder::class,
        ]);
    }
}