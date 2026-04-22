<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\EmailService;
use App\Http\Controllers\Concerns\LogsActivity;

class UserController extends Controller
{
    use LogsActivity;
    // Mostrar todos los usuarios (solo admin)
    public function index()
    {
        $users = User::all();
        return view('search_user', compact('users'));
    }

    // Actualizar el rol de un usuario
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|in:Usuario,Admin Inventario,Admin Mercado,Admin Facilidades,Admin Super',
        ]);

        $previousRole = $user->role;
        $user->role = $request->role;
        $user->save();

        $this->logActivity(
            'Cambiar rol de usuario',
            "Se cambió el rol del usuario {$user->email} (ID: {$user->id}) de '{$previousRole}' a '{$user->role}'"
        );

        return response()->json([
            'message' => 'Rol actualizado correctamente'
        ]);
    }

    /**
     * Email service used to send account-related notifications.
     * Keeps email logic separated from controller responsibilities.
     */
    protected $emailService;

    /**
     * Injects the EmailService dependency.
     * This allows the controller to delegate email sending
     * instead of handling it directly.
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }


    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|string|in:Activo,Bloqueado'
        ]);

        $previousStatus = $user->status;
        $user->status = $request->status;
        $user->save();

        $this->logActivity(
            'Cambiar estado de usuario',
            "Se cambió el estado del usuario {$user->email} (ID: {$user->id}) de '{$previousStatus}' a '{$user->status}'"
        );

        $superAdmin = User::where('role', 'Admin Super')
            ->where('status', 'Activo')
            ->first();

        $superAdminEmail = $superAdmin?->email ?? '+1 (787)-832-4040 Ext. 3841, 2008';

        /**
         * Send email when a user is banned.
         * Only triggers if the status actually changed to "Bloqueado".
         * Includes super admin contact information.
         */
        if ($request->status === 'Bloqueado' && $previousStatus !== 'Bloqueado') {
            $this->emailService->send(
                $user->email,
                'Cuenta bloqueada',
                'Tu cuenta ha sido bloqueada de la plataforma MAIKINE. Si entiendes que esto fue un error, comunícate con el super administrador (' . $superAdminEmail . ').'
            );
        }

        /**
         * Send email when a user is unbanned (reactivated).
         * Only triggers if the status actually changed to "Activo".
         */
        if ($request->status === 'Activo' && $previousStatus !== 'Activo') {
            $this->emailService->send(
                $user->email,
                'Cuenta desbloqueada',
                'Tu cuenta ha sido desbloqueada en la plataforma MAIKINE. Ya puedes acceder nuevamente y continuar utilizando los servicios con normalidad.'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'status' => $user->status
        ]);
    }
}
