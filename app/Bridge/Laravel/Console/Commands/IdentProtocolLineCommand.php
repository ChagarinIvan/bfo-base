<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\Clock;
use App\Models\IdentLine;
use App\Services\ClubsService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Будем определять людей из очереди на определение.
 * Запуск 4 раза в минуту
 * независимо от результата запись удаляется из очереди
 */
class IdentProtocolLineCommand extends Command
{
    protected $signature = 'protocol-lines:queue-ident';

    //стартует каждую минуту
    public function handle(
        RankService $rankService,
        ClubsService $clubsService,
        ProtocolLineService $protocolLineService,
        ProtocolLineIdentService $protocolLineIdentService,
        Clock $clock,
    ): void {
        $userId = (int) $this->argument('user_id');
        $identLine = IdentLine::first();
        if ($identLine) {
            $identLine->delete();
        } else {
            return;
        }

        $personId = $protocolLineIdentService->identPerson($identLine->ident_line);
        $protocolLines = $protocolLineService->getEqualLines($identLine->ident_line);

        if ($personId > 0) {
            $protocolLines->each(static function (ProtocolLine $protocolLine) use ($personId): void {
                $protocolLine->person_id = $personId;
                $protocolLine->save();
            });

            $rankService->reFillRanksForPerson($personId);
        } else {
            if ($protocolLines->isEmpty()) {
                return;
            }

            /** @var ProtocolLine $protocolLine */
            $protocolLine = $protocolLines->first();
            $person = new Person;
            $person->lastname = $protocolLine->lastname;
            $person->firstname = $protocolLine->firstname;

            try {
                if ($birthday = Carbon::createFromFormat('Y', (string) $protocolLine->year)) {
                    $person->birthday = $birthday->startOfYear();
                }
            } catch (InvalidFormatException) {
            }

            $club = $clubsService->findClub($protocolLine->club);
            if ($club) {
                $person->club_id = $club->id;
            }

            $person->created = $person->updated = new Impression($clock->now(), $userId);
            $person->create();

            $protocolLines->each(static function (ProtocolLine $protocolLine) use ($person): void {
                $protocolLine->person_id = $person->id;
                $protocolLine->save();
            });

            $rankService->reFillRanksForPerson($personId);
        }
    }

    protected function configure(): void
    {
        $this
            ->setName('protocol-lines:queue-ident')
            ->setDescription('Ident protocol line.')
            ->addArgument(
                'user_id',
                InputArgument::REQUIRED,
                'User Id for impression,'
            );
    }
}
