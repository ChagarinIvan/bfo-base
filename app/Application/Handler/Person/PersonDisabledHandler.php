<?php

declare(strict_types=1);

namespace App\Application\Handler\Person;

use App\Domain\Person\Event\PersonDisabled;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class PersonDisabledHandler implements ShouldQueue
{
    public function handle(PersonDisabled $event): void
    {
        $protocolLines = ProtocolLine::wherePersonId($event->person->id)->get();
        $protocolLines->each(static function (ProtocolLine $line): void {
            $line->person_id = null;
            $line->save();
        });
    }
}
