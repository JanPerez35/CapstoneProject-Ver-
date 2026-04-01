<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericMail;

class EmailController extends Controller
{
    public function showForm()
    {
        return view('kinventory');
    }

    public function sendEmail(Request $request)
    {
        // Obtener datos del JSON
        $data = $request->all();

        // Validación simple (compatible con fetch)
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

        // Enviar email
        Mail::to($data['email'])->queue(
            new GenericMail(
                $data['subject'],
                $data['message']
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Email enviado correctamente'
        ]);
    }
}
