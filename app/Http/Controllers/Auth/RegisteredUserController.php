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
        $role = $request->role; // 'admin' or 'supplier'

        // Base validation for all
        $rules = [
            'role'     => 'required|in:admin,supplier',
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8',
        ];

        // Buyer (admin) extra fields
        if ($role === 'admin') {
            $rules['contact_number']  = 'required|string|max:20';
            $rules['company_name']    = 'required|string|max:255';
            $rules['company_address'] = 'required|string|max:500';
            $rules['company_email']   = 'required|email|max:255';
            $rules['company_tel']     = 'required|string|max:20';
        }

        // Supplier extra fields
        if ($role === 'supplier') {
            $rules['company_name']    = 'required|string|max:255';
            $rules['company_address'] = 'required|string|max:255';
            $rules['company_city']    = 'required|string|max:255';
            $rules['company_zip']     = 'required|string|max:20';
            $rules['company_email']   = 'required|email|max:255';
            $rules['company_tel']     = 'required|string|max:20';
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'role'            => $validated['role'],
            'contact_number'  => $request->contact_number,
            'company_name'    => $request->company_name,
            'company_address' => $request->company_address,
            'company_city'    => $request->company_city,
            'company_zip'     => $request->company_zip,
            'company_email'   => $request->company_email,
            'company_tel'     => $request->company_tel,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}