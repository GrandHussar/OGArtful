<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->dropUnique(['date']); // Remove the unique constraint from the 'date' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->unique('date'); // Reapply the unique constraint if needed
        });
    }
};
