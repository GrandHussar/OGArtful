<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TherapySession extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'therapist_id', 'sessions_done', 'total_sessions'];

    // Relationship to the user (patient)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to the therapist
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}
