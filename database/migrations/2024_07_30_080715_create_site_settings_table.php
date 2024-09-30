<?php
// database/migrations/xxxx_xx_xx_create_site_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->nullable();
            $table->string('background_color')->default('#f0f0f0');
            $table->string('navbar_color')->default('#fdecdf');
            $table->string('button_color')->default('#efd6d5');
            $table->string('toolbar_color')->default('#ffffff');
            $table->string('icon_color', 7)->default('#000000');
            $table->string('parallax_image_path')->nullable();
            $table->json('carousel_image_paths')->nullable(); // Store multiple images as JSON
            $table->text('welcome_text')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_settings');
        
    }
}
