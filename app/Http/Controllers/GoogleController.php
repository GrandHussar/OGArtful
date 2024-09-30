<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->first();
    
            if ($finduser) {
                Auth::login($finduser);
                return redirect()->intended('dashboard');
            } else {
                // Generate a username from the email prefix
                $email = $user->email;
                $username = strstr($email, '@', true); // Extracts the part before '@'
    
                // Check if the generated username is unique
                $count = User::where('username', $username)->count();
                if ($count > 0) {
                    $username .= rand(1000, 9999); // Add random numbers if the username already exists
                }
    
                $newUser = User::create([
                    'username' => $username,
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'password' => encrypt('123456dummy'),
                    'roles' => 'user',
                    'name' => $user->name, // Full name from Google
                ]);
    
                Auth::login($newUser);
                return redirect()->intended('dashboard');
            }
    
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}