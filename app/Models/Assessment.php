<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'therapist_id', // Ensure therapist_id is fillable
        'comment',      // Add comment to fillable so it can be mass assigned
    ];

    // Define the relationship to the User model (the user being assessed)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship to the User model (the therapist)
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}
