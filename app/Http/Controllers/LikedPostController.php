<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Post;
use Inertia\Inertia;
class LikedPostController extends Controller
{
    public function toggle(Post $post)
    {
        // Toggle the like
        $post->likes()->toggle(auth()->id());

        // Return a JSON response indicating success
        return response()->json(['success' => true]);
    }
}