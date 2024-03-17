<?php

declare(strict_types=1);

namespace App\Domain\Person\Event;

use App\Domain\Competition\Competition;
use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class PersonCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Person $person)
    {
    }
}
