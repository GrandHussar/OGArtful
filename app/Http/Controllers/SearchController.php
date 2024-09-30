<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        // Perform search in Post model
        $posts = Post::where('title', 'like', '%' . $query . '%')
                     ->orWhere('description', 'like', '%' . $query . '%')
                     ->get();

        // Perform search in User model
        $users = User::where('name', 'like', '%' . $query . '%')
                     ->orWhere('email', 'like', '%' . $query . '%')
                     ->get();

        return response()->json([
            'posts' => $posts,
            'users' => $users
        ]);
    }
}
