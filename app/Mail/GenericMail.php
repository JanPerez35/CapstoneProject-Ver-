<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;

class GenericMail extends Mailable
{
    public $subjectText;
    public $messageText;

    public function __construct($subjectText, $messageText)
    {
        $this->subjectText = $subjectText;
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('emails.generic-mail');
    }
}
