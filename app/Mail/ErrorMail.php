<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ErrorMail extends Mailable
{
    /**
     * @var \Throwable
     */
    private \Throwable $error;

    public function __construct(\Throwable $exception)
    {
        $this->error = $exception;
    }

    public function build(): self
    {
        $email = 'Chagarin.Ivan@gmail.com';

        return $this->from('Chagarin_Ivan@tut.by')
            ->to($email)
            ->setAddress($email, $email)
            ->subject($this->error->getMessage())
            ->view('emails.error')
            ->with([
                'error' => $this->error,
            ]);
    }
}
