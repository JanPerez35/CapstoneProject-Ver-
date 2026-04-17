<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

/**
 * Class GenericMail
 *
 * Reusable Mailable class for sending simple text-based emails.
 *
 * Responsibilities:
 * - receives subject and message content dynamically
 * - passes data to the email Blade view
 * - builds the final email structure
 *
 * Data flow:
 * Controller/Service → GenericMail → Blade view (emails.generic-mail)
 */
class GenericMail extends Mailable
{
    /**
     * Subject line of the email.
     *
     * @var string
     */
    public $subjectText;

    /**
     * Body content of the email.
     *
     * @var string
     */
    public $messageText;


    /**
     * Create a new GenericMail instance.
     *
     * @param string $subjectText Subject of the email
     * @param string $messageText Body content of the email
     */
    public function __construct($subjectText, $messageText)
    {
        $this->subjectText = $subjectText;
        $this->messageText = $messageText;
    }

    /**
     * Build the email message.
     *
     * - sets the email subject
     * - binds data to the Blade view
     *
     * The view 'emails.generic-mail' will have access to:
     * - $subjectText
     * - $messageText
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('emails.generic-mail');
    }
}
