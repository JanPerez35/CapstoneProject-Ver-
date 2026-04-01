<?php
namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\GenericMail;

class EmailService
{
    public function send($email, $subject, $message)
    {
        Mail::to($email)->queue(
            new GenericMail($subject, $message)
        );
    }
}
