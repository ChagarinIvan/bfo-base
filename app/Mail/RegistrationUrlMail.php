<?php
declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;

class RegistrationUrlMail extends Mailable
{
    public function __construct(
        public string $email,
        public string $url,
    ) {
    }

    public function build(): self
    {
        return $this->from('Chagarin_Ivan@tut.by')
            ->to($this->email)
            ->setAddress($this->email, $this->email)
            ->subject('Registration')
            ->view('emails.registration')
            ->with(['url' => $this->url,]);
    }
}
