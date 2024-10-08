<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Mood;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Assessment;

class DashboardController extends Controller
{
    public function index()
    {
        $moods = Mood::where('user_id', Auth::id())->get();
        return Inertia::render('NewDashboard', [
            'moods' => $moods
        ]);
    }

    public function getAssessment(Request $request)
    {
        $userId = $request->input('user_id');
        
        $assessment = Assessment::where('user_id', $userId)
            ->with('therapist')  // Assuming therapist relationship is defined in Assessment model
            ->first();

        if ($assessment) {
            return response()->json([
                'comment' => $assessment->comment,
                'therapist_name' => $assessment->therapist->name ?? 'No therapist assigned',
            ]);
        } else {
            return response()->json(['message' => 'No assessment found'], 404);
        }
    }
    
    
    
    public function storeAssessment(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'comment' => 'required|string',
            'client_id' => 'required|exists:users,id',
        ]);

        // Create a new assessment
        $assessment = new Assessment();
        $assessment->user_id = $request->client_id; // This is the client (user being assessed)
        $assessment->therapist_id = auth()->user()->id; // Assign the therapist (currently logged-in user)
        $assessment->comment = $request->input('comment');
        $assessment->save();

        return redirect()->back()->with('success', 'Assessment submitted successfully.');
    }
    
    

    /**
     * Fetch moods data for the authenticated user.
     */
    
    public function index2()
    {
        // Define the role name you're looking for
        $roleName = 'user';

        // Fetch users with the specified role
        $users = User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->select('id', 'name')->get();

        return response()->json($users);
    }
    public function getMoods(Request $request)
    {
        // Get the recipientId from the request, default to the logged-in user's ID if not provided
        $clientId = $request->query('clientId', Auth::id());

        // Fetch moods for the specified user
        $moods = Mood::where('user_id', $clientId)->get();

        return response()->json($moods);
    }

    /**
     * Store or update a mood for a specific date.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'mood' => 'required|string|max:50',
        ]);

        $mood = Mood::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'date' => $request->date,
            ],
            [
                'mood' => $request->mood,
            ]
        );

        return Inertia::location(route('newdashboard'));
    }
}
