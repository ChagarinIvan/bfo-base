<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ErrorMail extends Mailable
{
    /**
     * @var \Exception
     */
    private \Exception $error;

    public function __construct(\Exception $exception)
    {
        $this->error = $exception;
    }

    public function build(): self
    {
        $email = 'Chagarin.Ivan@gmail.com';

        return $this->from('Chagarin_Ivan@tut.by')
            ->to($email)
            ->setAddress($email, $email)
            ->subject('Parsing error')
            ->view('emails.error')
            ->with([
                'error' => $this->error,
            ]);
    }
}
