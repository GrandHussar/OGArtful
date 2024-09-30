<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CommentController extends Controller
{
    public function createComment(Request $request)
    {
        $userID = Auth::user();
        Comment::create([
            'comments' => $request->comment,
            'post_id' => $request->post_id,
            'user_id' => $userID->id
        ]
        );
        return redirect()->route('dashboard', ['post' => $request->post_id]);
    }
}
