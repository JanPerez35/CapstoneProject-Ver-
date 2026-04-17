<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function show()
    {
        return view('terms_and_conditions');
    }

    public function accept(Request $request)
    {
        $user = auth()->user();

        $user->terms_accepted = true;
        $user->save();

        return redirect()->route('kinventory')
            ->with('success', 'Términos y condiciones aceptados correctamente.');
    }

    public function update(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'Admin Super') {
            abort(403, 'No autorizado');
        }

        $request->validate([
            'terms_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'terms_pdf.required' => 'Debes seleccionar un archivo PDF.',
            'terms_pdf.file' => 'El archivo seleccionado no es válido.',
            'terms_pdf.mimes' => 'Solo se permiten archivos PDF.',
            'terms_pdf.max' => 'El PDF no puede exceder 10 MB.',
        ]);

        $file = $request->file('terms_pdf');

        $destinationPath = public_path('documents');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, 'terms_conditions.pdf');

        return redirect()->back()
            ->with('terms_updated_success', 'Términos y condiciones actualizados correctamente.');
    }
}