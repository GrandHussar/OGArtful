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
        'available_time' => 'required|date_format:H:i', // Adjust as needed
    ]);

    AvailableDate::create([
        'therapist_id' => auth()->id(),
        'date' => $request->available_date,
        'time' => $request->available_time, // Should now have the correct value
    ]);

    return back()->with('message', 'Availability added successfully!');
}


    // Fetch availability for a therapist
    public function index()
    {
        $availabilities = AvailableDate::where('therapist_id', auth()->id())->get();
        return Inertia::location(route('newdashboard'));
    }

    // Fetch availability for clients to view
   // Fetch availability for clients to view
   public function getAvailableDates()
   {
       $dates = AvailableDate::select('id', 'therapist_id', 'date', 'time', 'created_at', 'updated_at')->get();
   
       // Log the fetched dates to check for any issues
       \Log::info('Fetched Available Dates:', $dates->toArray());
   
       // Convert time from 24-hour to 12-hour format with AM/PM
       $dates->transform(function ($date) {
           try {
               $date->time = \Carbon\Carbon::createFromFormat('H:i', $date->time)->format('g:i A'); // Convert to 12-hour format
           } catch (\Exception $e) {
               \Log::error('Error converting time format:', ['time' => $date->time, 'error' => $e->getMessage()]);
               return $date; // Return original date if error occurs
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