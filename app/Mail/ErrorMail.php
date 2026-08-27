<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Throwable;

class ErrorMail extends Mailable
{
    public function __construct(private readonly Throwable $error, private readonly string $url, private readonly string $previousUrl)
    {
    }

    public function build(): self
    {
        $email = 'Chagarin.Ivan@gmail.com';

        return $this->from(config('mail.error_email'))
            ->to($email)
            ->setAddress($email, $email)
            ->subject($this->error->getMessage())
            ->view('emails.error')
            ->with([
                'error' => $this->error,
                'url' => $this->url,
                'previous' => $this->previousUrl,
            ]);
    }
}
