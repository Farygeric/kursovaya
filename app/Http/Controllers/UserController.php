<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function index()
    {
        if (auth()->user()->role->name !== 'admin') {
            abort(403, 'Access denied. Admins only.');
        }

        $users = User::select('users.id', 'users.login', 'roles.name as role')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = User::create([
            'login' => $validated['login'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        return response()->json($user->only('id', 'login', 'role_id'), 201);
    }

    public function updateRole(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->role_id = $validated['role_id'];
        $user->save();

        return response()->json($user->only('id', 'login', 'role_id'));
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user(); 

        $validated = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($validated['old_password'], $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The provided password is incorrect.'],
            ]);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();
        return response()->json(['message' => 'Password updated successfully']);
    }

    public function resetPassword(Request $request, int $id)
    {
        if (auth()->user()->role->name !== 'admin') {
            abort(403, 'Only admin can reset passwords.');
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json([
            'message' => 'Password reset successfully for user: ' . $user->login,
            'user_id' => $user->id
        ]);
    }

    
    public function me()
    {
        $user = auth()->user();
        return response()->json([
            'id' => $user->id,
            'login' => $user->login,
            'role_id' => $user->role_id,
            'role' => $user->role->name ?? null,
        ]);
    }

    public function update(Request $request, int $id)
    {
        if (auth()->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Only admins can edit users'], 403);
        }

        $user = User::findOrFail($id);

        $rules = [
            'role_id' => 'nullable|exists:roles,id',
            'password' => 'nullable|string|min:6',
        ];

        if ($request->filled('password')) {
            $rules['confirm_password_change'] = 'required|accepted';
        }

        $validated = $request->validate($rules);

        if (isset($validated['role_id'])) {
            $user->role_id = $validated['role_id'];
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json($user->only('id', 'login', 'role_id'));
    }

    public function destroy(int $id)
    {
        if (auth()->user()->role->name !== 'admin') {
            return response()->json(['error' => 'Only admins can delete users'], 403);
        }

        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot delete yourself'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted', 'id' => $id]);
    }
}