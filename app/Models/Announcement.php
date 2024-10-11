<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Announcement extends Model
{
    use HasFactory;

    // The table associated with the model
    protected $table = 'announcements';

    // The attributes that are mass assignable
    protected $fillable = [
        'therapist_id',
        'title',
        'content',
    ];

    /**
     * Get the therapist who made the announcement.
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}