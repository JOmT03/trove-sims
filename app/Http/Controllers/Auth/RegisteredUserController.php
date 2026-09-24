<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

   public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users',
        'password' => 'required|confirmed|min:8',
    ]);

    // Split the single "Full Name" field into first/last, since that's
    // how the users table is actually structured
    $nameParts = explode(' ', trim($validated['name']), 2);
    $firstName = $nameParts[0];
    $lastName  = $nameParts[1] ?? '';

    $user = User::create([
        'first_name' => $firstName,
        'last_name'  => $lastName,
        'email'      => $validated['email'],
        'password'   => Hash::make($validated['password']),
        'role'       => 'Staff',
    ]);

    event(new Registered($user));
    Auth::login($user);

    return redirect()->route('dashboard');
}
}