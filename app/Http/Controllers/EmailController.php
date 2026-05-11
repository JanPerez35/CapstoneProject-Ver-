<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailService;
use App\Models\User;

/**
 * Class EmailController
 *
 * Handles all email-related actions within the application.
 *
 * Responsibilities:
 * - sending generic emails from forms
 * - sending system notifications (approved, denied, banned, etc.)
 * - retrieving admin contact emails when needed
 */
class EmailController extends Controller
{
    /**
     * Email service instance used to send emails.
     *
     * @var EmailService
     */
    protected $emailService;

    /**
     * Constructor with dependency injection.
     *
     * @param EmailService $emailService Service responsible for sending emails
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Displays the email form view.
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        return view('kinventory');
    }

    /**
     * Sends a generic email based on user input. For testing purposes.
     *
     * Validates that required fields exist:
     * - email (recipient)
     * - subject
     * - message
     *
     * @param Request $request Incoming HTTP request containing email data
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure
     */
    public function sendEmail(Request $request)
    {
        $data = $request->all();

        //General validation to ensure required fields exist
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

        //Send email using EmailService
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


    /**
     * Sends a notification email when a request is approved.
     *
     * @return string Confirmation message
     */
    public function requestApproved()
    {
        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Solicitud de item aprobada',
            'Tu solicitud de equipo deportivo fue aprobada satisfactoriamente. Por favor entra a tu perfíl de MAIKINE para más detalles.'
        );

        return 'Correo de solicitud aprobada enviado.';
    }

    /**
     * Sends a notification email when a request is denied.
     *
     * Retrieves the inventory admin email. If not available,
     * falls back to super admin, then to a default email.
     *
     * @return string Confirmation message
     */
    public function requestDenied()
    {
        //Attempt to get inventory admin or fallback admin
        $inventoryAdmin = User::where('role', 'Administrador de Inventario')->first();
        $superAdmin = User::where('role', 'Super Administrador')->first();

        $adminEmail = $inventoryAdmin?->email
            ?? $superAdmin?->email
            ?? 'admin.inventario@upr.edu';

        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Solicitud de item denegada',
            'Tu solicitud de equipo deportivo fue denegada. Por favor entra a tu perfíl de MAIKINE para más detalles. De tener alguna duda comuníquese con el administrador de inventario (' . $adminEmail . ').'
        );

        return 'Correo de solicitud especial denegada enviado.';
    }


    /**
     * Sends an email notification when a user is banned.
     *
     * @return string Confirmation message
     */
    public function userBanned()
    {
        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Cuenta bloqueada',
            'Tu cuenta ha sido bloqueada de la plataforma MAIKINE. Si entiendes que esto fue un error, comunícate con el super administrador (administrador@upr.edu).'
        );

        return 'Correo de cuenta suspendida enviado.';
    }

    /**
     * Sends an email notification when a user is unbanned.
     *
     * @return string Confirmation message
     */
    public function userUnbanned()
    {
        $this->emailService->send(
            'jan.perez21@upr.edu',
            'Cuenta desbloqueada',
            'Tu cuenta ha sido reactivada en la plataforma MAIKINE. Ya puedes acceder nuevamente y continuar utilizando los servicios con normalidad.'
        );

        return 'Correo de cuenta desbloqueada enviado.';
    }

}
