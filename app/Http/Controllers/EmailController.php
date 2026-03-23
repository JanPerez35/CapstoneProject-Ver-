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
$request->validate([
'email' => 'required|email',
'subject' => 'required',
'message' => 'required'
]);

    Mail::to($request->email)->queue(
        new GenericMail(
            $request->subject,
            $request->message
        )
    );

return back()->with('success', 'Email sent!');
}
}
