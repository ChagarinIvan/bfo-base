<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Event;
use Exception;
use Illuminate\Support\Facades\Mail;

class ParsingException extends Exception
{
    private ?Event $event;

    public function report()
    {
        $this->event;
        //send email
//        $test = Mail::send();
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }
}
