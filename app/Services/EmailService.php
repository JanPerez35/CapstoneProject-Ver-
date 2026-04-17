<?php
namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\GenericMail;

/**
 * Class EmailService
 *
 * Service responsible for handling email sending logic.
 *
 * Responsibilities:
 * - acts as an abstraction layer between controllers/jobs and Laravel Mail
 * - centralizes email sending behavior
 * - queues emails for asynchronous processing
 *
 */
class EmailService
{
    /**
     * Sends an email using the GenericMail mailable.
     *
     * The email is queued instead of sent immediately, improving
     * application performance and user experience.
     *
     * @param string $email   Recipient email address
     * @param string $subject Subject of the email
     * @param string $message Body content of the email
     *
     * @return void
     */
    public function send($email, $subject, $message)
    {
        // Queue email for asynchronous sending
        Mail::to($email)->queue(
            new GenericMail($subject, $message)
        );
    }
}
