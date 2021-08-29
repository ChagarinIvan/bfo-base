<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Mail\ErrorMail;
use App\Models\Event;
use Exception;
use Illuminate\Contracts\Mail\Mailer;

class ParsingException extends Exception
{
    private ?Event $event;

    public function report(Mailer $mailer)
    {
        $mailer->send(new ErrorMail($this));
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }
}
