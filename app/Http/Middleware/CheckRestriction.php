<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckRestriction
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->is_restricted && $user->restriction_end_at && Carbon::parse($user->restriction_end_at)->isFuture()) {
            session(['restriction_end_at' => $user->restriction_end_at]);
            return redirect()->route('restricted');
        }

        return $next($request);
    }
}
