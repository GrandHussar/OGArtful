<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'therapist_id', 'appointment_date', 'available_date_id', 'appointment_time', 'status', 'link'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    public function sessionReport()
    {
        return $this->hasOne(SessionReport::class);
    }

}
