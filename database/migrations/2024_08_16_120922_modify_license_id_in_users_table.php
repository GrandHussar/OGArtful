<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing unique index on license_id
            $table->dropUnique('users_license_id_unique');
            
            // Change the license_id column to string and re-add unique index
            $table->string('license_id', 7)->nullable()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert the license_id column to integer
            $table->integer('license_id')->nullable()->unique()->change();
        });
    }
};