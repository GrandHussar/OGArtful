<?php

namespace App\Http\Controllers;

use App\Models\AvailableDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AvailableDateController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Storing available date:', [
            'therapist_id' => auth()->id(),
            'date' => $request->available_date,
            'time' => $request->available_time,
        ]);

        $request->validate([
            'available_date' => 'required|date',
            'available_time' => 'required|date_format:H:i',
        ]);

        AvailableDate::create([
            'therapist_id' => auth()->id(),
            'date' => $request->available_date,
            'time' => $request->available_time,
        ]);

        return back()->with('message', 'Availability added successfully!');
    }

    // Fetch availability for a therapist
    public function index()
    {
        $availabilities = AvailableDate::where('therapist_id', auth()->id())->get();
        return Inertia::location(route('newdashboard'));
    }

    // Fetch availability for clients to view, excluding booked dates
    public function getAvailableDates()
    {
        // Fetch only dates that are not booked
        $dates = AvailableDate::select('id', 'therapist_id', 'date', 'time', 'created_at', 'updated_at')
            ->where('is_booked', false)
            ->get();

        // Log the fetched dates to check for any issues
        \Log::info('Fetched Available Dates:', $dates->toArray());

        // Convert time from 24-hour to 12-hour format with AM/PM
        $dates->transform(function ($date) {
            try {
                $date->time = \Carbon\Carbon::createFromFormat('H:i:s', $date->time)->format('g:i A');
            } catch (\Exception $e) {
                try {
                    // Attempt to parse with only hours and minutes if seconds are not expected
                    $date->time = \Carbon\Carbon::createFromFormat('H:i', $date->time)->format('g:i A');
                } catch (\Exception $e) {
                    \Log::error('Error converting time format:', ['time' => $date->time, 'error' => $e->getMessage()]);
                }
            }
            return $date;
        });

        return response()->json(['availableDates' => $dates]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        $availableDate = AvailableDate::findOrFail($id);
        $availableDate->update([
            'date' => $request->date,
            'time' => $request->time,
        ]);

        return redirect()->route('available-dates.index')->with('success', 'Date updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $availableDate = AvailableDate::findOrFail($id);
            $availableDate->delete();
            return response()->json(['message' => 'Date deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting available date with ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to delete date.'], 500);
        }
    }
}
