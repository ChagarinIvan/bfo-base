<?php
declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PasswordMail extends Mailable
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    public function build(): self
    {
        return $this->from('Chagarin_Ivan@tut.by')
            ->to($this->email)
            ->setAddress($this->email, $this->email)
            ->subject('Registration')
            ->view('emails.password')
            ->with(['password' => $this->password,]);
    }
}
