<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Add this line
use Illuminate\Support\Facades\Auth; // Add this line
class ArtworkController extends Controller
{
    public function saveDrawing(Request $request)
    {
        $request->validate([
            'drawingData' => 'required|string',
            'name' => 'required|string|min:3',
        ]);
    
        try {
            $name = $request->input('name'); // Retrieve the name from the request
            $data = json_decode($request->input('drawingData'), true);
            
            Log::info('Saving drawing:', ['name' => $name, 'data' => $data]);
    
            $artwork = Artwork::create([
                'user_id' => Auth::id(),
                'name' => $name,
                'data' => json_encode($data), // Convert the array to a JSON string
            ]);
    
            Log::info('Artwork created:', ['name' => $artwork->name]); // Log the created artwork's name
    
            return response()->json(['id' => $artwork->id, 'name' => $artwork->name]);
        } catch (\Exception $e) {
            Log::error('Error saving drawing: ' . $e->getMessage());
            return response()->json(['error' => 'Error saving drawing'], 500);
        }
    }
    public function loadDrawing($id)
    {
        try {
            $artwork = Artwork::find($id);
            if ($artwork) {
                $drawingData = json_decode($artwork->data);
                Log::info('Loading drawing:', ['data' => $drawingData]);
                return response()->json($drawingData);
            }
            return response()->json(['error' => 'Drawing not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error loading drawing: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading drawing'], 500);
        }
    }

    public function loadSavedItems()
    {
        try {
            $savedItems = Artwork::where('user_id', Auth::id())->get();
            Log::info('Loading saved items:', ['savedItems' => $savedItems->toArray()]);
            return response()->json($savedItems);
        } catch (\Exception $e) {
            Log::error('Error loading saved items: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading saved items'], 500);
        }
    }
}
