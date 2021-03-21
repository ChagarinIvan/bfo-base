<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Mail\ErrorMail;
use App\Models\Event;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class ParsingException extends Exception
{
    private ?Event $event;

    public function report()
    {
        Mail::to(Config::get('ERROR_EMAIL'))->send(new ErrorMail($this));
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
