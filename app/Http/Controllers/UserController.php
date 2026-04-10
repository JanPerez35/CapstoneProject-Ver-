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
        return view('search_user', compact('users'));
    }

    // Actualizar el rol de un usuario
    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|string|in:Usuario,Admin Inventario,Admin Mercado,Admin Facilidades,Admin Super',]);
        $user->role = $request->role;
        $user->save();

        return response()->json([
                'message' => 'Rol actualizado correctamente'
            ]);    
    }
}