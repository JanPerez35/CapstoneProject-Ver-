<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class TermsController
 *
 * Handles the Terms and Conditions workflow of the application.
 *
 * Responsibilities:
 * - displaying the Terms and Conditions page
 * - recording when an authenticated user accepts the terms
 * - allowing an Admin Super to upload and replace the official PDF document
 */
class TermsController extends Controller
{
    /**
     * Displays the Terms and Conditions view.
     *
     * This method returns the page where users can read the
     * current Terms and Conditions document before accepting it.
     *
     * @return \Illuminate\View\View
     */

    use LogsActivity;

    /**
     * Displays the Terms page used during the login / acceptance flow.
     *
     * This version does not allow updating the PDF.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('terms_and_conditions', [
            'allowUpdate' => false,
        ]);
    }

    /**
     * Displays the Terms page from the footer.
     *
     * This version allows the Admin Super to update the PDF document.
     *
     * @return \Illuminate\View\View
     */
    public function showFromFooter()
    {
        $allowUpdate = auth()->check() && auth()->user()->role === 'Admin Super';

        return view('terms_and_conditions', [
            'allowUpdate' => $allowUpdate,
        ]);
    }

    /**
     * Records that the authenticated user has accepted the Terms and Conditions.
     *
     * This method updates the current user's `terms_accepted` field to true
     * and saves the change in the database. After that, the user is redirected
     * to the kinventory route with a success message.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accept(Request $request)
    {
        $user = auth()->user();

        $user->terms_accepted = true;
        $user->save();

        $this->logActivity(
            'Aceptar términos y condiciones',
            "El usuario {$user->email} aceptó los términos y condiciones."
        );

        return redirect()->route('kinemarket')
            ->with('success', 'Términos y condiciones aceptados correctamente.');
    }

    /**
     * Updates the Terms and Conditions PDF file.
     *
     * This action is restricted to users with the role "Admin Super".
     * It validates that the uploaded file is a PDF with a maximum size
     * of 10 MB, ensures the destination directory exists, and then
     * replaces the current Terms and Conditions document with the new file.
     *
     * The uploaded file is stored in:
     * public/documents/terms_conditions.pdf
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Only Admin Super users are authorized to update the terms document
        if (!auth()->check() || auth()->user()->role !== 'Admin Super') {
            abort(403, 'No autorizado');
        }

        // Validate uploaded file
        $request->validate([
            'terms_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'terms_pdf.required' => 'Debes seleccionar un archivo PDF.',
            'terms_pdf.file' => 'El archivo seleccionado no es válido.',
            'terms_pdf.mimes' => 'Solo se permiten archivos PDF.',
            'terms_pdf.max' => 'El PDF no puede exceder 2 MB.',
        ]);

        $file = $request->file('terms_pdf');
        $originalName = $file->getClientOriginalName();

        $file->storeAs('public/documents', 'terms_conditions.pdf');

        $this->logActivity(
            'Actualizar términos y condiciones',
            "Se actualizó el PDF de términos y condiciones con el archivo: {$originalName}"
        );

        User::query()->update(['terms_accepted' => false]);

        // Ensure the destination folder exists
        $destinationPath = public_path('documents');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Replace the current terms document with the new uploaded version
        $file->move($destinationPath, 'terms_conditions.pdf');

        return redirect()->back()
            ->with('terms_updated_success', 'Términos y condiciones actualizados correctamente.');
    }
}
