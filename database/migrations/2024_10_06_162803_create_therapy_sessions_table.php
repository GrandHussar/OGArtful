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
        Schema::create('therapy_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User receiving the therapy
            $table->foreignId('therapist_id')->constrained('users')->onDelete('cascade'); // Therapist providing the sessions
            $table->integer('sessions_done')->default(0); // Completed sessions
            $table->integer('total_sessions'); // Total number of sessions
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('therapy_sessions');
    }
    
};
