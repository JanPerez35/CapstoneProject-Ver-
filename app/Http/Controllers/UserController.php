<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Mostrar todos los usuarios (solo admin)
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    // Actualizar el rol de un usuario
    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|string|in:user,inventory_admin,marketplace_admin,facility_admin,super_admin',]);
        $user->role = $request->role;
        $user->save();

        return redirect()->back()->with('success', 'Role updated successfully!');
    }
}