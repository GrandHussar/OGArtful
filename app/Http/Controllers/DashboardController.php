<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Mood;
use Illuminate\Support\Facades\Auth;
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
    public function getMoods()
    {
        $moods = Mood::where('user_id', Auth::id())->get();
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
