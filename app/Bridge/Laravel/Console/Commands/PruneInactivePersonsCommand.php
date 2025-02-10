<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonSearchDto;
use App\Application\Service\Person\DisablePerson;
use App\Application\Service\Person\DisablePersonService;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Services\ProtocolLineIdentService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use function time;

/**
 * Каманда для выдалення людзей без стартаў (выпадкова створанныя).
 * Запускаем раз в день
 */
class PruneInactivePersonsCommand extends Command
{
    protected $signature = 'persons:prune';

    public function __construct(
        private readonly ListPersonsService $listPersonsService,
        private readonly DisablePersonService $disablePersonService,
    ) {
        parent::__construct();
    }

    public function handle(ProtocolLineIdentService $identService): void
    {
        $this->info('Start');
        $userId = (int) $this->argument('user_id');

        $count = 0;

        $persons = $this->listPersonsService->execute(
            new ListPersons(new PersonSearchDto(withoutLinesAndPayments: true))
        );

        foreach ($persons as $person) {
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
            ->addArgument(
                'user_id',
                InputArgument::REQUIRED,
                'User Id for impression,'
            )
        ;
    }
}
