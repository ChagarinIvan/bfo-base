<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\SearchPersonDto;
use App\Application\Service\Person\DisablePerson;
use App\Application\Service\Person\DisablePersonService;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Каманда для выдалення людзей без стартаў (выпадкова створанныя).
 * Запускаем раз в день
 */
#[Signature('persons:prune {userId}')]
class PruneInactivePersonsCommand extends Command
{
    private const int INFINITY = 10_000;

    public function __construct(
        private readonly ListPersonsService $listPersonsService,
        private readonly DisablePersonService $disablePersonService,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Start');
        $userId = (int) $this->argument('userId');

        $count = 0;

        $persons = $this->listPersonsService->execute(
            new ListPersons(new SearchPersonDto(withoutLinesAndPayments: true)),
        );

        foreach ($persons->setPerPage(self::INFINITY)->getIterator() as $person) {
            $this->disablePersonService->execute(new DisablePerson($person->id, new UserId($userId)));
            $count++;
        }

        $this->info('Disabled persons count is ' . $count);
        $this->info("Finish");
    }

    protected function configure(): void
    {
        $this
            ->setName('persons:prune')
            ->setDescription('Prune persons.')
        ;
    }
}
