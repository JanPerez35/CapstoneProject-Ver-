<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailService;

class EmailController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function showForm()
    {
        return view('kinventory');
    }

    public function sendEmail(Request $request)
    {
        $data = $request->all();

        if (
            !isset($data['email']) ||
            !isset($data['subject']) ||
            !isset($data['message'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Datos incompletos'
            ], 400);
        }

        $this->emailService->send(
            $data['email'],
            $data['subject'],
            $data['message']
        );

        return response()->json([
            'success' => true,
            'message' => 'Email enviado correctamente'
        ]);
    }

    public function requestApproved()
    {
        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Solicitud de item aprobada',
            'Tu solicitud de equipo deportivo fue aprobada satisfactoriamente. Por favor entra a tu perfíl de MAIKINE para más detalles.'
        );

        return 'Correo de solicitud aprobada enviado.';
    }

    public function requestDenied()
    {
        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Solicitud de item denegada',
            'Tu solicitud de equipo deportivo fue denegada. Por favor entra a tu perfíl de MAIKINE para más detalles. De tener alguna duda comuniquese con el administrador de inventario (inventario@upr.edu). '
        );

        return 'Correo de solicitud especial denegada enviado.';
    }

    public function userBanned()
    {
        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Cuenta bloqueada',
            'Tu cuenta ha sido bloqueada de la plataforma MAIKINE. Si entiendes que esto fue un error, comunícate con el super administrador (administrador@upr.edu).'
        );

        return 'Correo de cuenta suspendida enviado.';
    }

    public function userUnbanned()
    {
        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Cuenta desbloqueada',
            'Tu cuenta ha sido reactivada en la plataforma MAIKINE. Ya puedes acceder nuevamente y continuar utilizando los servicios con normalidad.'
        );

        return 'Correo de cuenta desbloqueada enviado.';
    }

    public function unreadMessagesReminder()
    {
        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Tienes mensajes sin leer',
            'Hola, tienes mensajes sin leer en MAIKINE. Entra a la plataforma para revisarlos.'
        );

        return 'Correo de recordatorio de mensajes sin leer enviado.';
    }
}
