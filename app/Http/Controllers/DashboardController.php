<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Mood;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $moods = Mood::where('user_id', Auth::id())->get();
        return Inertia::render('NewDashboard', [
            'moods' => $moods
        ]);
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
        $recipientId = $request->query('recipientId', Auth::id());

        // Fetch moods for the specified user
        $moods = Mood::where('user_id', $recipientId)->get();

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
