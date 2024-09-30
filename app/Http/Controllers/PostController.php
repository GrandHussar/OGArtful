<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Comment;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\PostImage;
use App\Models\TemporaryImage;
use App\Models\User;
class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $authUser = auth()->user();
        
    //     // Apply pagination before retrieving results
    //     $query = Post::with('user', 'postImages') // Load user and post images
    //         ->latest()
    //         ->withCount('likes')
    //         ->withCount(['likes as liked' => function($q) {
    //             $q->where('user_id', auth()->id());
    //         }])
    //         ->withCasts(['liked' => 'boolean']);
    
    //     // Paginate the results
    //     $paginatedPosts = $query->paginate(5);
    
    //     // Map through the paginated collection
    //     $posts = $paginatedPosts->getCollection()
    //         ->map(function ($post) use ($authUser) {
    //             $post->isFollowing = $authUser->follows($post->user);
    //             // Include additional user information for chat
    //             return $post;
    //         });
    
    //     // Replace the collection in the paginated result
    //     $paginatedPosts->setCollection($posts);
    
    //     $comments = Comment::with('user')->get()->groupBy('post_id');
    
    //     return Inertia::render('Dashboard', [
    //         'posts' => PostResource::collection($paginatedPosts),
    //         'comments' => $comments,
    //     ]);
    // }
    public function index()
    {
        $authUser = auth()->user();
    
        // Build the query for posts
        $query = Post::with('user', 'postImages')
            ->latest()
            ->withCount('likes')
            ->withCount(['likes as liked' => function($q) {
                $q->where('user_id', auth()->id());
            }])
            ->withCasts(['liked' => 'boolean']);
    
        // Paginate posts
        $paginatedPosts = $query->paginate(5);
    
        // Map additional properties to posts
        $posts = $paginatedPosts->getCollection()
            ->map(function ($post) use ($authUser) {
                $post->isFollowing = $authUser->follows($post->user);
                return $post;
            });
    
        $paginatedPosts->setCollection($posts);
    
        // Get comments grouped by post ID
        $comments = Comment::with('user')->get()->groupBy('post_id');
    
        // Get top users
        $topUsers = User::withCount('followers')->orderBy('followers_count', 'desc')->take(5)->get();
    
        // Return data to Inertia
        return Inertia::render('Dashboard', [
            'posts' => PostResource::collection($paginatedPosts),
            'comments' => $comments,
            'topUsers' => $topUsers,
        ]);
    }
    //Show a single post
    public function showPost(Post $post)
    {
        $user = Auth::user();
        return Inertia::render('Posts/Show-Post', ['post' => $post, 'user' => $user]);
    }
    //Show a single post
    public function deletePost($id)
    {
        Log::info('Delete post request received for post ID: ' . $id);

        $post = Post::findOrFail($id);
        $postImages = PostImage::where('post_id', $id)->get();
        $directoriesToDelete = [];

        foreach ($postImages as $postImage) {
            $imagePath = $postImage->post_image_path;
            
            if (Storage::exists('images/' . $imagePath)) {
                Storage::delete('images/' . $imagePath);
                
        
                $directory = dirname($imagePath);
                if (!in_array($directory, $directoriesToDelete)) {
                    $directoriesToDelete[] = $directory;
                }
            }
        }

        PostImage::where('post_id', $id)->delete();
        $post->delete();
        foreach ($directoriesToDelete as $directory) {
            if (Storage::exists('images/' . $directory) && Storage::allFiles('images/' . $directory) == []) {
                Storage::deleteDirectory('images/' . $directory);
            }
        }

        Log::info('Post deleted: ' . $id);

        return Inertia::render(route('dashboard'));
    }

    //Send request to controller to create post
    public function uploadPost(Request $request)
    {   
        $userID = Auth::user();
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Validation for multiple images
        ]);

        // Create the post
        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $userID->id
        ]);

        // Handle multiple image uploads
        $temporaryImages = TemporaryImage::whereIn('folder', $request->image_url)->get();
        foreach($temporaryImages as $temporaryImage) {
            Storage::copy('images/tmp/' . $temporaryImage->folder . '/' . $temporaryImage->file, 'images/' . $temporaryImage->folder . '/' . $temporaryImage->file);
            PostImage::create(
                [
                    'post_id' => $post->id,
                    'post_image_caption' => $temporaryImage->file,
                    'post_image_path' => $temporaryImage->folder . '/' . $temporaryImage->file
                ]
                );
                Storage::deleteDirectory('images/tmp/' . $temporaryImage->folder);
                $temporaryImage->delete();
            }
        return to_route('dashboard');
    }

    public function sharePost(Request $request)
    {   
        $userID = Auth::user();
    
        // Validate request data
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required'],
        ]);
    
        $imageUrl = $request->image_url;
    
        // Extract filename from the URL
        $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
    
        // Create a new post
        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $userID->id,
        ]);
    
        // Create a new PostImage record
        PostImage::create([
            'post_id' => $post->id,
            'post_image_caption' => $filename, // Store the filename for caption
            'post_image_path' => $filename, // Store the filename for path
        ]);
    
        return to_route('dashboard');
    }


    //Show Edit Post Page
    public function updatePost(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $data = $request->only(['title', 'description']);

        // Preserve existing image URL if no new image is uploaded
        if ($request->hasFile('image_url')) {
            $file = $request->file('image_url');
            $path = $file->store('images', 'public');
            $data['image_url'] = $path;
        } else {
            $data['image_url'] = $post->image_url;
        }

        $post->update($data);

        return to_route('dashboard');
    }

}
