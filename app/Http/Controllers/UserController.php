<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\EmailService;



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

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    //Temporary update status for gmail testing
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|string|in:Activo,Bloqueado'
        ]);

        $previousStatus = $user->status;
        $user->status = $request->status;
        $user->save();

        if ($request->status === 'Bloqueado' && $previousStatus !== 'Bloqueado') {
            $this->emailService->send(
                $user->email,
                'Cuenta bloqueada',
                'Tu cuenta ha sido bloqueada de la plataforma MAIKINE. Si entiendes que esto fue un error, comunícate con el super administrador (administrador@upr.edu).'
            );
        }

        if ($request->status === 'Activo' && $previousStatus !== 'Activo') {
            $this->emailService->send(
                $user->email,
                'Cuenta desbloqueada',
                'Tu cuenta ha sido reactivada en la plataforma MAIKINE. Ya puedes acceder nuevamente y continuar utilizando los servicios con normalidad.'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $user->status
        ]);
    }
}
