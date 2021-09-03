<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\IdentLine;
use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use App\Services\RankService;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Будем определять людей из очереди на определение.
 * 3 человека в минуту
 * независимо от результата запись удаляется из очереди
 *
 * * * * * php /var/www/blog/artisan protocol-lines:queue-ident
 * * * * * (sleep 20; php /var/www/blog/artisan protocol-lines:queue-ident)
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
        if ($identLine) {
            $identLine->delete();
        } else {
            return;
        }

        $rankService = app(RankService::class);
        $identService = app(IdentService::class);

        $personId = ($identService)->identPerson($identLine->ident_line);
        $protocolLines = ProtocolLine::wherePreparedLine($identLine->ident_line)->get();

        if ($personId > 0) {
            $protocolLines->each(function (ProtocolLine $protocolLine) use ($personId, $rankService) {
                $protocolLine->person_id = $personId;
                $protocolLine->save();
                $rankService->fillRank($protocolLine);
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
            try {
                if ($birthday = Carbon::createFromFormat('Y', $protocolLine->year)) {
                    $person->birthday = $birthday->startOfYear();
                }
            } catch (InvalidFormatException) {}

            $club = Club::whereName($protocolLine->club)->get();
            if ($club->count() > 0) {
                $person->club_id = $club->first()->id;
            }
            $person->save();
            $person->makePrompts();

            $protocolLines->each(function (ProtocolLine $protocolLine) use ($person, $rankService) {
                $protocolLine->person_id = $person->id;
                $protocolLine->save();
                $rankService->fillRank($protocolLine);
            });
        }
    }
}
