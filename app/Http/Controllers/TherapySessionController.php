<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TherapySession;
use Auth;

class TherapySessionController extends Controller
{
    // Fetch the therapy session for a specific user or the current logged-in user
    public function index(Request $request)
    {
        try {
            $userId = $request->query('user_id') ?? Auth::id(); // Get user ID from request or use the current logged-in user
    
            $therapySession = TherapySession::where('user_id', $userId)->first();
    
            // Check if session exists
            if (!$therapySession) {
                return response()->json([
                    'message' => 'No therapy session found', 
                    'sessions_done' => 0, 
                    'total_sessions' => 0
                ], 200); // Respond with default values
            }
    
            return response()->json($therapySession, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch therapy session', 'message' => $e->getMessage()], 500);
        }
    }
    

    // Update the therapy session data
    public function update(Request $request, $id)
    {
        try {
            // Check if the therapy session exists
            $therapySession = TherapySession::firstOrCreate(
                ['user_id' => $id], // Condition to find the session
                [
                    'therapist_id' => Auth::id(), // If not found, create a new session with these attributes
                    'total_sessions' => $request->input('total_sessions', 10), // Default total sessions if not provided
                ]
            );
    
            // Ensure that the authenticated user is the therapist for the session
            if (Auth::id() != $therapySession->therapist_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
    
            // Validate the incoming request data
            $request->validate([
                'sessions_done' => 'required|integer|min:0|max:' . $therapySession->total_sessions,
            ]);
    
            // Update the session progress
            $therapySession->sessions_done = $request->input('sessions_done');
            $therapySession->save();
    
            return response()->json(['success' => 'Therapy session updated successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('TherapySession update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update therapy session', 'message' => $e->getMessage()], 500);
        }
    }
    
    
    
    
}



