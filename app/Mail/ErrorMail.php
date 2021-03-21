<?php

declare(strict_types=1);

namespace App\Mail;

use App\Exceptions\ParsingException;
use Illuminate\Mail\Mailable;

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

        return $this->from('support@orient.by')
            ->subject('Parsing error')
            ->view('emails.error')
            ->with([
                'eventName' => $event->name,
                'competitionName' => $competition->name,
                'error' => $this->error->getMessage(),
            ]);
    }
}
