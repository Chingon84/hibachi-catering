<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('admin.team.index', compact('users'));
    }

    public function create()
    {
        $user = new User();
        return view('admin.team.form', compact('user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'username' => ['nullable','string','max:255','unique:users,username'],
            'position' => ['nullable','string','max:255'],
            'role' => ['required','string','max:50', Rule::in(['owner','admin','manager','staff','readonly','office'])],
            'password' => ['required','string','min:6'],
            'can_access_admin' => ['sometimes','boolean'],
            'is_active' => ['sometimes','boolean'],
        ]);

        // Only owner can create another owner
        if (($data['role'] ?? '') === 'owner' && !Auth::user()->isOwner()) {
            abort(403, 'Only owner can assign owner role');
        }

        $data['password'] = Hash::make($data['password']);
        $data['can_access_admin'] = (bool)($data['can_access_admin'] ?? false);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        User::create($data);

        return redirect()->route('admin.team.index')->with('ok', 'User created');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.team.form', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'username' => ['nullable','string','max:255', Rule::unique('users','username')->ignore($user->id)],
            'position' => ['nullable','string','max:255'],
            'role' => ['required','string','max:50', Rule::in(['owner','admin','manager','staff','readonly','office'])],
            'password' => ['nullable','string','min:6'],
            'can_access_admin' => ['sometimes','boolean'],
            'is_active' => ['sometimes','boolean'],
        ]);

        // Prevent modifying the owner role unless the current user is owner
        if ($user->isOwner() && !Auth::user()->isOwner()) {
            abort(403, 'Only owner can modify owner');
        }
        if (($data['role'] ?? '') === 'owner' && !Auth::user()->isOwner()) {
            abort(403, 'Only owner can assign owner role');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['can_access_admin'] = (bool)($data['can_access_admin'] ?? false);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $user->update($data);

        return redirect()->route('admin.team.index')->with('ok', 'User updated');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->isOwner()) {
            abort(403, 'Owner cannot be deleted');
        }
        $user->delete();
        return redirect()->route('admin.team.index')->with('ok', 'User deleted');
    }

    public function toggleAccess($id)
    {
        $user = User::findOrFail($id);
        if ($user->isOwner()) {
            abort(403, 'Owner access cannot be toggled');
        }
        $user->can_access_admin = !$user->can_access_admin;
        $user->save();
        return back()->with('ok', 'Access updated');
    }
}
