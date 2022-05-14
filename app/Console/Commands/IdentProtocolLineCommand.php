<?php

namespace App\Console\Commands;

use App\Models\IdentLine;
use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\ClubsService;
use App\Services\PersonsService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Будем определять людей из очереди на определение.
 * Запуск несколько раз в неделю
 * независимо от результата запись удаляется из очереди
 */
class IdentProtocolLineCommand extends Command
{
    protected $signature = 'protocol-lines:queue-ident';

    //стартует каждую минуту
    public function handle(
        RankService $rankService,
        PersonsService $personsService,
        ClubsService $clubsService,
        ProtocolLineService $protocolLineService,
        ProtocolLineIdentService $protocolLineIdentService
    ): void {
        $identLine = IdentLine::first();
        if ($identLine) {
            $identLine->delete();
        } else {
            return;
        }

        $personId = $protocolLineIdentService->identPerson($identLine->ident_line);
        $protocolLines = $protocolLineService->getEqualLines($identLine->ident_line);

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

            $club = $clubsService->findClub($protocolLine->club);
            if ($club) {
                $person->club_id = $club->id;
            }

            $person = $personsService->storePerson($person);

            $protocolLines->each(function (ProtocolLine $protocolLine) use ($person, $rankService) {
                $protocolLine->person_id = $person->id;
                $protocolLine->save();
                $rankService->fillRank($protocolLine);
            });
        }
    }
}
