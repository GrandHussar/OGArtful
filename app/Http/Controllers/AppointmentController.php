<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AvailableDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'appointment_date' => 'required|date',
        'appointment_time' => 'required',
        'therapist_id' => 'required|exists:users,id',
        'link' => 'nullable|url', // Validate the link if provided
    ]);

    // Convert appointment_time to 24-hour format
    $formattedTime = Carbon::createFromFormat('g:i A', $request->appointment_time)->format('H:i:s');

    // Check if the appointment date and therapist's availability is not booked
    $availableDate = AvailableDate::where('therapist_id', $request->therapist_id)
        ->where('date', $request->appointment_date)
        ->where('is_booked', false)
        ->first();

    if (!$availableDate) {
        return response()->json(['error' => 'This appointment date is not available.'], 400);
    }

    // Retrieve the available_date_id
    $availableDateId = $availableDate->id;
    \Log::info("Available Date ID: {$availableDateId}");

    // Create the appointment with available_date_id
    $appointment = Appointment::create([
        'user_id' => Auth::id(),
        'therapist_id' => $request->therapist_id,
        'appointment_date' => $request->appointment_date,
        'available_date_id' => $availableDateId, 
        'appointment_time' => $formattedTime,
        'status' => 'pending',
        'link' => $request->link, // Include the link if provided
    ]);

    // Mark the date as booked
    $availableDate->update(['is_booked' => true]);

    return response()->json(['appointment' => $appointment]);
}


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $appointment = Appointment::findOrFail($id);

        // Update the appointment status
        $appointment->update([
            'status' => $request->status,
        ]);

        return response()->json(['appointment' => $appointment]);
    }

    public function updateLink(Request $request, $id)
    {
        $request->validate([
            'link' => 'nullable|url', // Validate link if provided
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update(['link' => $request->link]);

        return response()->json(['appointment' => $appointment]);
    }

    public function indexForTherapist()
    {
        $appointments = Appointment::with('user')
            ->where('therapist_id', Auth::id())
            ->get();

        return response()->json(['appointments' => $appointments]);
    }

    public function allAppointmentsIndex(Request $request)
    {
        try {
            // Log: Check user ID
            $userId = Auth::id();
            \Log::info("Fetching all appointments for user ID: {$userId}");
    
            // Fetch all appointments for the authenticated user
            $appointments = Appointment::where('user_id', $userId)->get();
    
            // Log: Check if appointments are being retrieved
            \Log::info("Fetched all appointments: ", $appointments->toArray());
    
            return response()->json([
                'appointments' => $appointments
            ], 200);
    
        } catch (\Exception $e) {
            \Log::error("Error fetching all appointments: " . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch appointments.'], 500);
        }
    }
    
}
