<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SiteSetting::create([
            'background_color' => '#f0f0f0',
            'navbar_color' => '#fdecdf',
            'button_color' => '#efd6d5',
            'toolbar_color' => '#ffffff',
            'icon_color' => '#000000',
            'welcome_text' => 'Unleash your creativity, draw anything you want.'
        ]);
    }
}
