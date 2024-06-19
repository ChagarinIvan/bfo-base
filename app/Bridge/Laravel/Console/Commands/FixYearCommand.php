<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonDto;
use App\Application\Dto\Person\PersonInfoDto;
use App\Application\Dto\Person\PersonSearchDto;
use App\Application\Service\Person\AddPerson;
use App\Application\Service\Person\AddPersonService;
use App\Application\Service\Person\Exception\FailedToAddPerson;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Application\Service\Person\UpdatePersonInfo;
use App\Application\Service\Person\UpdatePersonInfoService;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Domain\Person\Citizenship;
use App\Domain\Person\PersonRepository;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Models\IdentLine;
use App\Services\ClubsService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use function array_shift;
use function count;
use function sprintf;

final class FixYearCommand extends Command
{
    protected $signature = 'persons:fix-age';

    public function __construct(
        private readonly UpdatePersonInfoService $service,
        private readonly ListPersonsService $persons,
        private readonly ViewPersonService $person,
    ) {
        parent::__construct();
    }

    public function handle(
        RankService $rankService,
        ClubsService $clubsService,
        ProtocolLineService $protocolLineService,
        ProtocolLineIdentService $protocolLineIdentService,
    ): void {
        $this->info('Start');
        $userId = (int) $this->argument('user_id');
        $year = $this->argument('year');
        $persons = $this->persons->execute(new ListPersons(new PersonSearchDto(year: $year)));
        $this->info(sprintf('Has %d persons', count($persons)));

        foreach ($persons as $person) {
            $person = $this->person->execute(new ViewPerson($person->id, true));
            $groupedByYearProtocolLines = $person->groupedByYearProtocolLines;
            $protocolLines = array_shift($groupedByYearProtocolLines);
            $info = new PersonInfoDto();
            $info->firstname = $person->firstname;
            $info->lastname = $person->lastname;
            $info->citizenship = $person->citizenship;
            $info->birthday = sprintf('%d-01-01', array_shift($protocolLines)->year);
            $info->clubId = $person->clubId;

            $this->service->execute(new UpdatePersonInfo($person->id, $info, new UserId($userId)));
        }
    }

    protected function configure(): void
    {
        $this
            ->setName('persons:fix-age')
            ->addArgument(
                'user_id',
                InputArgument::REQUIRED,
                'User Id for impression,'
            )
            ->addArgument(
                'year',
                InputArgument::REQUIRED,
                'year'
            )
        ;
    }
}
