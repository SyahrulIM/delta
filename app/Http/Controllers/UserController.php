<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index'); // blade yang kita buat di bawah
    }

    // Data untuk DataTable (JSON)
    public function data()
    {
        $users = User::orderBy('id', 'desc')->get();

        $rows = $users->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'role' => $p->role,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|string|max:255',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->password = Hash::make(12345678);

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User created',
            'data' => $user,
        ]);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated',
            'data' => $user,
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted'
        ]);
    }
}
