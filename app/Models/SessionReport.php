<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'duration',
        'activity_type',
        'other_activity',
        'engagement_level',
        'observed_emotions',
        'artistic_quality',
        'artwork_theme',
        'other_theme',
        'shared_significant_thoughts',
        'thoughts_detail',
        'therapeutic_techniques',
        'mental_state',
        'recommendations',
        'additional_notes'
    ];

    protected $casts = [
        'observed_emotions' => 'array',
        'therapeutic_techniques' => 'array',
        'shared_significant_thoughts' => 'boolean',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}