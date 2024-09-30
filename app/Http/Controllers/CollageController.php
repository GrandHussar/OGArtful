<?php

namespace App\Http\Controllers;

use App\Models\Collage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CollageController extends Controller
{
    // public function saveCollageJson(Request $request)
    // {
    //     $request->validate([
    //         'title' => 'required|string',
    //         'elements' => 'required|array',
    //         'brushStrokes' => 'nullable|array',
    //         'backgroundColor' => 'nullable|string',
    //         'width' => 'required|numeric',
    //         'height' => 'required|numeric',
    //     ]);
    
    //     $user = $request->user();
    
    //     $collageData = [
    //         'elements' => $request->input('elements'),
    //         'brushStrokes' => $request->input('brushStrokes', []),
    //     ];
    
    //     $collage = new Collage();
    //     $collage->user_id = $user->id;
    //     $collage->title = $request->input('title');
    //     $collage->data = json_encode($collageData);  // Properly encode data
    //     $collage->width = $request->input('width');
    //     $collage->height = $request->input('height');
    //     $collage->background_color = $request->input('backgroundColor', '#ffffff');
    //     $collage->save();
    
    //     return response()->json(['success' => true, 'collage_id' => $collage->id]);
    // }
    public function saveCollageJson(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'elements' => 'required|array',
            'brushStrokes' => 'nullable|array',
            'backgroundColor' => 'nullable|string',
            'template' => 'nullable|string', // Expecting URL
            'width' => 'required|numeric',
            'height' => 'required|numeric',
        ]);
    
        $user = $request->user();
    
        // Handle template image URL saving
        $templatePath = $request->input('template'); // Directly use URL
    
        $collageData = [
            'elements' => $request->input('elements'),
            'brushStrokes' => $request->input('brushStrokes', []),
        ];
    
        $collage = new Collage();
        $collage->user_id = $user->id;
        $collage->title = $request->input('title');
        $collage->data = json_encode($collageData);
        $collage->width = $request->input('width');
        $collage->height = $request->input('height');
        $collage->background_color = $request->input('backgroundColor', '#ffffff');
        $collage->template = $templatePath; // Store the URL directly
        $collage->save();
    
        return response()->json(['success' => true, 'collage_id' => $collage->id]);
    }
    
    

public function loadCollage($id)
{
    // Fetch the collage from the database
    $collage = Collage::find($id);

    if (!$collage) {
        return response()->json(['success' => false, 'message' => 'Collage not found'], 404);
    }

    // Decode the elements and brush strokes from the collage data
    $data = json_decode($collage->data, true);

    // Return the response with all necessary data
    return response()->json([
        'success' => true,
        'elements' => $data['elements'] ?? [],
        'brushStrokes' => $data['brushStrokes'] ?? [],
        'width' => $collage->width,
        'height' => $collage->height,
        'background_color' => $collage->background_color ?? '#ffffff', // Default to white if no color is provided
        'template' => $collage->template, // Include the template image path
    ]);
}
    
    
    
    

    public function index(Request $request)
    {
        $user = $request->user();
        $collages = Collage::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'collages' => $collages,
        ]);
    }

    public function destroy($id)
    {
        try {
            $collage = Collage::find($id);
    
            if (!$collage) {
                return response()->json(['success' => false, 'message' => 'Collage not found'], 404);
            }
    
            // Delete the collage record from the database
            $collage->delete();
    
            return response()->json(['success' => true, 'message' => 'Collage deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Error deleting collage: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
    
            return response()->json(['success' => false, 'message' => 'Server error occurred'], 500);
        }
    }
    
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('images', 'public'); 
            $url = asset('storage/' . $path);
            return response()->json(['url' => $url], 200);
        } else {
            return response()->json(['error' => 'No image uploaded'], 400);
        }
    }
    
}