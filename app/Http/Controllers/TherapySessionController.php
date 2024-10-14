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
            // Find or create the therapy session for the user
            $therapySession = TherapySession::firstOrCreate(
                ['user_id' => $id],
                [
                    'therapist_id' => Auth::id(),  // Assign therapist if creating a new session
                    'total_sessions' => $request->input('total_sessions', 10), // Default if not provided
                    'sessions_done' => 0,  // Initialize sessions_done to 0 if created
                ]
            );
    
            // Ensure the authenticated user is the assigned therapist
            if (Auth::id() != $therapySession->therapist_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
    
            // Validate the request data
            $request->validate([
                'sessions_done' => 'required|integer|min:0|max:' . $therapySession->total_sessions,
                'total_sessions' => 'required|integer|min:1',  // Ensure total_sessions is valid
            ]);
    
            // Update the session progress and total sessions if needed
            $therapySession->sessions_done = $request->input('sessions_done');
            $therapySession->total_sessions = $request->input('total_sessions', $therapySession->total_sessions);
            $therapySession->save();
    
            return response()->json(['success' => 'Therapy session updated successfully'], 200);
        } catch (\Exception $e) {
            // Log any errors for debugging
            \Log::error('TherapySession update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update therapy session', 'message' => $e->getMessage()], 500);
        }
    }
    
    
    
}



