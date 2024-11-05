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
            'observed_emotions' => 'nullable|array',
            'observed_emotions.*' => 'string',
            'artistic_quality' => 'required|in:excellent,good,fair,poor',
            'artwork_theme' => 'required|in:positive,negative,neutral,other',
            'other_theme' => 'nullable|string',
            'shared_significant_thoughts' => 'required|boolean',
            'thoughts_detail' => 'nullable|string',
            'therapeutic_techniques' => 'nullable|array',
            'therapeutic_techniques.*' => 'string',
            'mental_state' => 'required|in:improved,stable,deteriorated',
            'recommendations' => 'nullable|string',
            'additional_notes' => 'nullable|string',
        ]);

        $appointment = Appointment::findOrFail($appointmentId);

        $sessionReport = $appointment->sessionReport()->create([
            'duration' => $request->duration,
            'activity_type' => $request->activity_type,
            'other_activity' => $request->other_activity,
            'engagement_level' => $request->engagement_level,
            'observed_emotions' => $request->observed_emotions,
            'artistic_quality' => $request->artistic_quality,
            'artwork_theme' => $request->artwork_theme,
            'other_theme' => $request->other_theme,
            'shared_significant_thoughts' => $request->shared_significant_thoughts,
            'thoughts_detail' => $request->thoughts_detail,
            'therapeutic_techniques' => $request->therapeutic_techniques,
            'mental_state' => $request->mental_state,
            'recommendations' => $request->recommendations,
            'additional_notes' => $request->additional_notes,
        ]);

        return response()->json(['session_report' => $sessionReport], 201);
    }
    public function getPatientSessionSummary($therapistId, $patientId)
    {
        // Fetch all sessions associated with both the therapist and the patient
        $appointments = Appointment::where('therapist_id', $therapistId)
            ->where('user_id', $patientId)
            ->with('sessionReport')
            ->get();

        // Filter only appointments that have session reports
        $sessionReports = $appointments->pluck('sessionReport')->filter();

        // Initialize counters for each mental state
        $mentalStateCounts = [
            'improved' => 0,
            'stable' => 0,
            'deteriorated' => 0
        ];

        // Count each mental state
        foreach ($sessionReports as $report) {
            if ($report && isset($mentalStateCounts[$report->mental_state])) {
                $mentalStateCounts[$report->mental_state]++;
            }
        }

        // Return the data as a JSON response for testing
        return response()->json([
            'patientId' => $patientId,
            'therapistId' => $therapistId,
            'totalSessions' => $sessionReports->count(),
            'mentalStateCounts' => $mentalStateCounts
        ], 200);
    }
}
