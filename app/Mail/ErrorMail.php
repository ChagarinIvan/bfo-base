<?php

declare(strict_types=1);

namespace App\Mail;

use App\Exceptions\ParsingException;
use App\Models\Competition;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;

class ErrorMail extends Mailable
{
    /**
     * @var ParsingException
     */
    private ParsingException $error;

    public function __construct(ParsingException $exception)
    {
        $this->error = $exception;
    }

    public function build(): self
    {
        $event = $this->error->getEvent();
        $competition = Competition::find($event->competition_id);
        $email = 'Chagarin.Ivan@gmail.com';

        return $this->from('Chagarin_Ivan@tut.by')
            ->to($email)
            ->setAddress($email, $email)
            ->subject('Parsing error')
            ->view('emails.error')
            ->with([
                'eventName' => $event->name,
                'competitionName' => $competition->name,
                'error' => $this->error->getMessage(),
            ]);
    }
}
