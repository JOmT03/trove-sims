<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByRaw("FIELD(role,'Owner','Manager','Staff')")
                      ->orderBy('first_name')->orderBy('last_name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'role'       => 'required|in:Manager,Staff',
            'password'   => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = new User();
        $user->first_name = $data['first_name'];
        $user->last_name  = $data['last_name'];
        $user->email      = $data['email'];
        $user->role       = $data['role'];
        $user->password   = Hash::make($data['password']);
        $user->is_active  = true;
        $user->save();

        return redirect()->route('users.index')
            ->with('success', $data['first_name'].' '.$data['last_name'].' added as '.$data['role'].'.');
    }

    public function edit(User $user)
    {
        if ($user->role === 'Owner') {
            return redirect()->route('users.index')->with('error', 'The Owner account cannot be edited here.');
        }
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role === 'Owner') {
            return redirect()->route('users.index')->with('error', 'The Owner account cannot be edited here.');
        }
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email,'.$user->id,
            'role'       => 'required|in:Manager,Staff',
            'password'   => ['nullable', 'confirmed', Password::min(6)],
        ]);

        $user->first_name = $data['first_name'];
        $user->last_name  = $data['last_name'];
        $user->email      = $data['email'];
        $user->role       = $data['role'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('users.index')->with('success', $user->first_name.' '.$user->last_name.' updated.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->role === 'Owner') {
            return redirect()->route('users.index')->with('error', 'The Owner account cannot be deactivated.');
        }
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'You cannot deactivate your own account.');
        }
        $user->is_active = !$user->is_active;
        $user->save();
        $state = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('users.index')->with('success', $user->first_name.' '.$state.'.');
    }
}