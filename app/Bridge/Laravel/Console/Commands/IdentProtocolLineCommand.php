<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonDto;
use App\Application\Dto\Person\PersonInfoDto;
use App\Application\Service\Person\AddPerson;
use App\Application\Service\Person\AddPersonService;
use App\Application\Service\Person\Exception\FailedToAddPerson;
use App\Domain\Person\Citizenship;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Models\IdentLine;
use App\Services\ClubsService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use function sprintf;

/**
 * Будем определять людей из очереди на определение.
 * Запуск 4 раза в минуту
 * независимо от результата запись удаляется из очереди
 */
final class IdentProtocolLineCommand extends Command
{
    protected $signature = 'protocol-lines:queue-ident';

    public function __construct(
        private readonly AddPersonService $addPersonService,
    ) {
        parent::__construct();
    }

    public function handle(
        RankService $rankService,
        ClubsService $clubsService,
        ProtocolLineService $protocolLineService,
        ProtocolLineIdentService $protocolLineIdentService,
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
            $club = $clubsService->findClub($protocolLine->club);

            $personInfo = new PersonInfoDto;
            $personInfo->lastname = $protocolLine->lastname;
            $personInfo->firstname = $protocolLine->firstname;
            $personInfo->birthday = $protocolLine->year ? sprintf('%d-01-01', $protocolLine->year)  : null;
            $personInfo->clubId = $club ? (string) $club->id : null;
            $personInfo->citizenship = Citizenship::BELARUS->value;
            $personDto = new PersonDto;
            $personDto->info = $personInfo;

            try {
                $person = $this->addPersonService->execute(new AddPerson($personDto, new UserId($userId)));
                $personId = (int) $person->id;
            } catch (FailedToAddPerson $e) {
                $personId = $e->previousPersonId;
            }

            $protocolLines->each(static function (ProtocolLine $protocolLine) use ($personId): void {
                $protocolLine->person_id = $personId;
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
