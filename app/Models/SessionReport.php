<?php

// app/Models/SessionReport.php

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
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
