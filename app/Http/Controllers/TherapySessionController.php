<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Auth;

class TherapySessionController extends Controller
{
    // Fetch therapy session summary based on appointments
    public function index(Request $request)
    {
        try {
            $userId = $request->query('user_id') ?? Auth::id(); // Default to current logged-in user

            // Calculate completed, pending, and canceled appointments
            $completedAppointments = Appointment::where('user_id', $userId)->where('status', 'completed')->count();
            $pendingAppointments = Appointment::where('user_id', $userId)->where('status', 'pending')->count();
            $canceledAppointments = Appointment::where('user_id', $userId)->where('status', 'canceled')->count();
            $totalSessions = $completedAppointments + $pendingAppointments; // Only active sessions

            // Return structured response with appointment summary
            return response()->json([
                'sessions_done' => $completedAppointments,
                'total_sessions' => $totalSessions,
                'pending' => $pendingAppointments,
                'completed' => $completedAppointments,
                'canceled' => $canceledAppointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch therapy session data', 
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
