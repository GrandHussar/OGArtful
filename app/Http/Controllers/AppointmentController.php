<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'therapist_id' => 'required|exists:users,id',
        ]);

        $appointment = Appointment::create([
            'user_id' => auth()->id(),
            'therapist_id' => $request->therapist_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
        ]);

        return response()->json(['appointment' => $appointment]);
    }

    public function indexForTherapist()
    {
        $appointments = Appointment::with('user')
            ->where('therapist_id', auth()->id())
            ->get();

        return response()->json(['appointments' => $appointments]);
    }
}