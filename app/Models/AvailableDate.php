<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableDate extends Model
{
    use HasFactory;

    protected $fillable = ['therapist_id', 'date', 'time','is_booked'];

    public function therapist()
    {
        return $this->belongsTo(User::class);
    }
}
