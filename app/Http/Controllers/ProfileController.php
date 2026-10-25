<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated())->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Accounts are never deleted: the Owner or a Manager deactivates them in Users,
     * so the records a user created keep their history.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort(403, 'Accounts are deactivated by the Owner or a Manager, not deleted.');
    }
}
