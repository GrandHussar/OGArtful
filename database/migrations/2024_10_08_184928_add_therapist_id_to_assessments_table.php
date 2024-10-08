<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->unsignedBigInteger('therapist_id')->nullable(); // Assuming therapists are users
            $table->foreign('therapist_id')->references('id')->on('users');
        });
    }
    
    public function down()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['therapist_id']);
            $table->dropColumn('therapist_id');
        });
    }
    
};
