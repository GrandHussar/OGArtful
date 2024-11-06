<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Mood;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Assessment;
use App\Models\Announcement;
use Illuminate\Support\Facades\Log;
class DashboardController extends Controller
{
    public function index()
    {
        $moods = Mood::where('user_id', Auth::id())->get();
        return Inertia::render('NewDashboard', [
            'moods' => $moods
        ]);
    }


    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Announcement::create([
            'therapist_id' => Auth::id(), // The currently authenticated therapist
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Announcement created successfully!'], 201);
    }
    public function updateAssessment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $assessment = Assessment::findOrFail($id);

        // Ensure the therapist owns this assessment
        if ($assessment->therapist_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $assessment->comment = $request->comment;
        $assessment->save();

        return response()->json(['message' => 'Assessment updated successfully!']);
    }

    // Delete assessment
    public function destroyAssessment($id)
    {
        $assessment = Assessment::findOrFail($id);

        // Ensure the therapist owns this assessment
        if ($assessment->therapist_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $assessment->delete();

        return response()->json(['message' => 'Assessment deleted successfully!']);
    }

    // Method to get all announcements
    public function getAnnouncement()
    {
        $allAnnouncements = Announcement::with('therapist')->orderBy('created_at', 'desc')->get();
        $userAnnouncements = Announcement::where('therapist_id', Auth::id())->get();

        // Log the queries/data for debugging
        \Log::info('All Announcements:', $allAnnouncements->toArray());
        \Log::info('User Announcements:', $userAnnouncements->toArray());

        if ($allAnnouncements->isNotEmpty()) {
            return response()->json([
                'allAnnouncements' => $allAnnouncements,
                'userAnnouncements' => $userAnnouncements,
            ]);
        } else {
            return response()->json([
                'announcements' => [], // Returning an empty array
                'message' => 'No announcements found',
            ], 200);
        }
    }
    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

    return response()->json(['message' => 'Announcement updated successfully!']);
    }
    public function destroyAnnouncement(Announcement $announcement)
    {
        $announcement->delete();
        return response()->json(['message' => 'Announcement deleted successfully!']);
    }
    public function getAssessment(Request $request)
    {
        $userId = $request->input('user_id');

        $assessments = Assessment::where('user_id', $userId)
            ->with('therapist')  // Assuming therapist relationship is defined in the Assessment model
            ->get();

        if ($assessments->isNotEmpty()) {
            return response()->json([
                'assessments' => $assessments->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,  // Ensure id is included
                        'comment' => $assessment->comment,
                        'therapist_name' => $assessment->therapist->name ?? 'No therapist assigned',
                        'created_at' => $assessment->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ]);
        } else {
            return response()->json([
                'assessments' => [],
                'message' => 'No assessments found',
            ], 200);
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
         // Fetch users with the "user" role only (exclude admin and therapist)
         $users = User::whereHas('roles', function ($query) {
             $query->where('name', 'therapist');
         })->select('id', 'name')->get();

         return response()->json($users);
     }

     public function index3()
     {
         // Fetch users with the "user" role only (exclude admin and therapist)
         $users = User::whereHas('roles', function ($query) {
             $query->where('name', 'user');
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

    public function updateMoods(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'mood' => 'required|string|in:happy,sad,neutral,excited,angry',
            'date' => 'required|date',
        ]);

        // Find the mood entry by user_id and date
        $mood = Mood::where('user_id', Auth::id())
                    ->where('date', $request->date)
                    ->first();

        if (!$mood) {
            return response()->json(['error' => 'Mood entry not found'], 404); // Not found
        }

        // Ensure the authenticated user owns the mood entry
        if ($mood->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403); // Forbidden
        }

        // Update the mood
        $mood->update([
            'mood' => $request->mood,
        ]);

        return Inertia::location(route('newdashboard'));
    }

}
