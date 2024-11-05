<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionReportsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('session_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->integer('duration')->nullable(); // Duration decided by the therapist
            $table->enum('activity_type', ['drawing', 'painting', 'animation', 'collage', 'other'])->nullable();
            $table->string('other_activity')->nullable(); // If 'other' is selected
            $table->enum('engagement_level', ['not engaged', 'somewhat engaged', 'moderately engaged', 'highly engaged'])->nullable();
            $table->json('observed_emotions')->nullable(); // Store emotions as a JSON array
            $table->enum('artistic_quality', ['excellent', 'good', 'fair', 'poor'])->nullable();
            $table->enum('artwork_theme', ['positive', 'negative', 'neutral', 'other'])->nullable();
            $table->string('other_theme')->nullable();
            $table->boolean('shared_significant_thoughts')->default(false);
            $table->text('thoughts_detail')->nullable();
            $table->json('therapeutic_techniques')->nullable(); // Store techniques as a JSON array
            $table->enum('mental_state', ['improved', 'stable', 'deteriorated'])->nullable();
            $table->text('recommendations')->nullable();
            $table->text('additional_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_reports');
    }
}
