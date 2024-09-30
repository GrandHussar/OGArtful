<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
class FollowerController extends Controller
{ 
    public function toggle(User $user)
{
    $follower = Auth::user();
    $follower->followings()->toggle($user->id);

    // Fetch the updated following state
    $followingUserIds = $follower->followings->pluck('id');

    return response()->json(['userIds' => $followingUserIds]);
}

    public function fetchFollowingUsers(User $user)
    {
        // Check if the user exists
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Fetch following users' IDs
        $followingUserIds = $user->followings->pluck('id');

        // Return the data in JSON format
        return response()->json(['userIds' => $followingUserIds]);
    }

}
