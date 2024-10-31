<?php
// app/Http/Controllers/SessionReportController.php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\SessionReport;
use Illuminate\Http\Request;

class SessionReportController extends Controller
{
    public function store(Request $request, $appointmentId)
    {
        $request->validate([
            'duration' => 'required|integer',
            'activity_type' => 'required|in:drawing,painting,animation,collage,other',
            'other_activity' => 'nullable|string|required_if:activity_type,other',
            'engagement_level' => 'required|in:not engaged,somewhat engaged,moderately engaged,highly engaged',
        ]);

        $appointment = Appointment::findOrFail($appointmentId);

        $sessionReport = $appointment->sessionReport()->create([
            'duration' => $request->duration,
            'activity_type' => $request->activity_type,
            'other_activity' => $request->other_activity,
            'engagement_level' => $request->engagement_level,
        ]);

        return response()->json(['session_report' => $sessionReport]);
    }
}
