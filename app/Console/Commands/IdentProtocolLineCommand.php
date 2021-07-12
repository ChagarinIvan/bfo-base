<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\IdentLine;
use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Будем определять людей из очереди на определение.
 * 2 человека в минуту
 * независимо от результата запись удаляется из очереди
 *
 * * * * * php /var/www/blog/artisan protocol-lines:queue-ident
 * * * * * (sleep 30; php /var/www/blog/artisan protocol-lines:queue-ident)
 *
 * Class IdentProtocolLineCommand
 * @package App\Console\Commands
 */
class IdentProtocolLineCommand extends Command
{
    protected $signature = 'protocol-lines:queue-ident';

    //стартует каждую минуту
    public function handle(): void
    {
        $identLine = IdentLine::first();
        $identLine->delete();

        $personId = (new IdentService())->identPerson($identLine->ident_line);
        $protocolLines = ProtocolLine::wherePreparedLine($identLine->ident_line)->get();

        if ($personId > 0) {
            $protocolLines->each(function (ProtocolLine $protocolLine) use ($personId) {
                $protocolLine->person_id = $personId;
                $protocolLine->save();
            });
        } else {
            if ($protocolLines->isEmpty()) {
                return;
            }
            /** @var ProtocolLine $protocolLine */
            $protocolLine = $protocolLines->first();
            $person = new Person();
            $person->lastname = $protocolLine->lastname;
            $person->firstname = $protocolLine->firstname;
            if ($birthday = Carbon::createFromFormat('Y', $protocolLine->year)) {
                $person->birthday = $birthday->startOfYear();
            }
            $club = Club::whereName($protocolLine->club)->get();
            if ($club->count() > 0) {
                $person->club_id = $club->first()->id;
            }
            $person->save();
            $person->makePrompts();
        }
    }
}
