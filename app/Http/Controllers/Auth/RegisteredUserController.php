<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function choose(): Response
    {
        return Inertia::render('Auth/Register-Landing-Page');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function store(Request $request): RedirectResponse
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'username' => 'required|string|max:255',
    //         'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
    //         'password' => ['required', 'confirmed', Rules\Password::defaults()],
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'username' => $request->username,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     event(new Registered($user));

    //     Auth::login($user);

    //     return redirect(route('dashboard', absolute: false));
    // }

    public function storeUser(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        $user->assignRole('user');
    
        event(new Registered($user));
    
        Auth::login($user);
    
        // Redirect to a specific route or verification page
        if ($user->hasVerifiedEmail()) {
            return redirect(route('dashboard', absolute: false));
        } else {
            return redirect()->route('verification.notice');
        }
    }

    public function storeTherapist(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'license_id' => ['required', 'string', 'size:7', 'unique:'.User::class],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'license_id' => $request->license_id,
        ]);

        $user->assignRole('therapist');

        event(new Registered($user));
    
        Auth::login($user);

        if ($user->hasVerifiedEmail()) {
            return redirect(route('dashboard', absolute: false));
        } else {
            return redirect()->route('verification.notice');
        }
    }

    public function getUserById($id)
    {
        $user = User::find($id);
    
        if ($user) {
            return response()->json([
                'id' => $user->id,
                'username' => $user->username,
            ]);
        }
    
        return response()->json(['message' => 'User not found'], 404);
    }

    /**
     * Search users based on the provided criteria.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $users = User::with('roles')
            ->whereHas('roles', function($query) {
                $query->whereNotIn('name', ['super_admin', 'it_admin']);
            })
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%");
            })
            ->get();

        return Inertia::render('ManageUsers', [
            'users' => $users,
        ]);
    }
    public function indexTherapist()
    {
        $users = User::all(); // Or add filtering logic if needed
        return response()->json($users);
    }


    /**
     * Update user details.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->hasRole(['super_admin', 'it_admin'])) {
            return back()->with('error', 'Cannot edit this user.');
        }

        $currentUser = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'role' => ['required', 'string', function ($attribute, $value, $fail) use ($currentUser) {
                if ($currentUser->hasRole('super_admin') && !in_array($value, ['user', 'therapist', 'it_admin'])) {
                    $fail('Invalid role.');
                } elseif ($currentUser->hasRole('it_admin') && !in_array($value, ['user', 'therapist'])) {
                    $fail('Invalid role.');
                }
            }],
            'is_restricted' => 'required|boolean',
            'restriction_end_at' => 'nullable|date',
        ]);

        $user->update($validated);
        $user->syncRoles([$validated['role']]);
        $user->save();

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Restrict or unrestrict a user.
     */
    public function restrict(User $user): RedirectResponse
    {
        if ($user->hasRole(['super_admin', 'it_admin'])) {
            return back()->with('error', 'Cannot restrict this user.');
        }

        $user->is_restricted = !$user->is_restricted;
        $user->restriction_end_at = $user->is_restricted ? now()->addDays(7) : null;
        $user->save();

        return back()->with('success', 'User restriction status updated successfully.');
    }
}
