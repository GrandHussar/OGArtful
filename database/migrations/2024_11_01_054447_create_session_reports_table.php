<?php

// database/migrations/xxxx_xx_xx_create_session_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionReportsTable extends Migration
{
    public function up()
    {
        Schema::create('session_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->integer('duration')->nullable(); // Duration decided by the therapist
            $table->enum('activity_type', ['drawing', 'painting', 'animation', 'collage', 'other'])->nullable();
            $table->string('other_activity')->nullable(); // If 'other' is selected
            $table->enum('engagement_level', ['not engaged', 'somewhat engaged', 'moderately engaged', 'highly engaged'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('session_reports');
    }
}
