<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\LegacySearchPersonDto;
use App\Application\Dto\Person\PersonInfoDto;
use App\Application\Service\Person\ListLegacyPersons;
use App\Application\Service\Person\ListLegacyPersonsService;
use App\Application\Service\Person\UpdatePersonInfo;
use App\Application\Service\Person\UpdatePersonInfoService;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use function array_shift;
use function count;
use function sprintf;

#[Signature('persons:fix-age')]
final class FixYearCommand extends Command
{
    public function __construct(
        private readonly UpdatePersonInfoService $service,
        private readonly ListLegacyPersonsService $persons,
        private readonly ViewPersonService $person,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Start');
        $userId = (int) $this->argument('user_id');
        $year = $this->argument('year');
        $persons = $this->persons->execute(new ListLegacyPersons(new LegacySearchPersonDto(year: $year)));
        $this->info(sprintf('Has %d persons', count($persons)));

        foreach ($persons as $person) {
            $person = $this->person->execute(new ViewPerson($person->id, includeProtocolLines: true));
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
