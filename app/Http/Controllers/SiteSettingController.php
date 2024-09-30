<?php
namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function edit()
    {
        $siteSetting = SiteSetting::first();
        return Inertia::render('EditSiteSetting', [
            'siteSetting' => $siteSetting,
        ]);
    }
    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'background_color' => 'required|string|max:7',
            'navbar_color' => 'required|string|max:7',
            'button_color' => 'required|string|max:7',
            'toolbar_color' => 'required|string|max:7',
            'icon_color' => 'required|string|max:7',
            'parallax_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'carousel_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'welcome_text' => 'nullable|string',
        ]);
    
        $siteSetting = SiteSetting::first();
    
        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($siteSetting->logo_path) {
                Storage::disk('public')->delete($siteSetting->logo_path);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
            $siteSetting->logo_path = $logoPath;
        }
    
        // Handle parallax image upload
        if ($request->hasFile('parallax_image')) {
            if ($siteSetting->parallax_image_path) {
                Storage::disk('public')->delete($siteSetting->parallax_image_path);
            }
            $parallaxPath = $request->file('parallax_image')->store('images', 'public');
            $siteSetting->parallax_image_path = $parallaxPath;
        }
    
        // Handle carousel images upload
        if ($request->hasFile('carousel_images')) {
            // Delete existing carousel images
            if ($siteSetting->carousel_image_paths) {
                foreach ($siteSetting->carousel_image_paths as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
    
            $carouselPaths = [];
            foreach ($request->file('carousel_images') as $image) {
                $carouselPaths[] = $image->store('carousel_images', 'public'); // Ensure images are stored in the correct directory
            }
            $siteSetting->carousel_image_paths = $carouselPaths;
        }
    
        // Update other settings
        $siteSetting->background_color = $request->background_color;
        $siteSetting->navbar_color = $request->navbar_color;
        $siteSetting->button_color = $request->button_color;
        $siteSetting->toolbar_color = $request->toolbar_color;
        $siteSetting->icon_color = $request->icon_color;
        $siteSetting->welcome_text = $request->welcome_text;
    
        $siteSetting->save();
    
        return redirect()->route('site-settings.edit')->with('success', 'Settings updated successfully.');
    }
    



    public function getCarouselImages()
    {
        $carouselDirectory = 'carousel_images';
        $files = Storage::disk('public')->files($carouselDirectory);

        // Strip out directory path to just return the file names
        $files = array_map(function ($file) use ($carouselDirectory) {
            return str_replace("$carouselDirectory/", '', $file);
        }, $files);

        return response()->json($files);
    }
}
