<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\PostImage;
use App\Models\TemporaryImage;
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function page(User $user): Response
{
    $authUser = auth()->user();

    $posts = Post::where('user_id', $user->id) // Filter posts by the user's ID
        ->with(['user', 'postImages']) // Eager load user and postImages relationships
        ->latest()
        ->withCount('likes')
        ->withCount(['likes as liked' => function ($q) {
            $q->where('user_id', auth()->id());
        }])
        ->withCasts(['liked' => 'boolean'])
        ->paginate(5);

    $comments = Comment::with('user')->get()->groupBy('post_id');

    // Get follower and following counts
    $followersCount = $user->getFollowersCount();
    $followingCount = $user->getFollowingsCount();

    // Determine if the authenticated user is following the profile user
    $isFollowing = $authUser ? $authUser->follows($user) : false;

    return Inertia::render('Profile/ProfilePage', [
        'posts' => PostResource::collection($posts),
        'comments' => $comments,
        'user' => $user,
        'followersCount' => $followersCount,
        'followingCount' => $followingCount,
        'isFollowing' => $isFollowing, // Pass follow status to the view
        'authUserId' => $authUser->id, // Pass the authenticated user's ID
    ]);
}

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $temporaryImages = TemporaryImage::whereIn('folder', $request->profile_image_url)->get();
        foreach ($temporaryImages as $temporaryImage) {
            // Copy the temporary image to the permanent directory
            Storage::copy('images/tmp/' . $temporaryImage->folder . '/' . $temporaryImage->file, 'images/' . $temporaryImage->folder . '/' . $temporaryImage->file);

            // Update the user's profile_image_url with the new image path
            $request->user()->update([
                'profile_image_url' => $temporaryImage->folder . '/' . $temporaryImage->file,
            ]);

            // Delete the temporary directory and image record
            Storage::deleteDirectory('images/tmp/' . $temporaryImage->folder);
            $temporaryImage->delete();
        }

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
