<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ErrorMail extends Mailable
{
    /**
     * @var \Throwable
     */
    private \Throwable $error;
    private string $url;
    private string $previousUrl;

    public function __construct(\Throwable $exception, string $url, string $previousUrl)
    {
        $this->error = $exception;
        $this->url = $url;
        $this->previousUrl = $previousUrl;
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
                'url' => $this->url,
                'previous' => $this->previousUrl,
            ]);
    }
}
