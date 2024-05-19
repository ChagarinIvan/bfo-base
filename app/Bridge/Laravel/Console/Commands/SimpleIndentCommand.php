<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonSearchDto;
use App\Application\Service\Person\DisablePerson;
use App\Application\Service\Person\DisablePersonService;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Services\ProtocolLineIdentService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use function time;

/**
 * Команда для определения людей с помощью прямого совпадения идентификатора.
 * Запускаем раз в день
 */
class SimpleIndentCommand extends Command
{
    protected $signature = 'protocol-lines:simple-ident';

    public function __construct(
        private ListPersonsService $listPersonsService,
        private DisablePersonService $disablePersonService,
    ) {
        parent::__construct();
    }

    public function handle(ProtocolLineIdentService $identService): void
    {
        $this->info('Start');
        $userId = (int) $this->argument('user_id');

        $startTime = time();
        $protocolLines = ProtocolLine::whereNull('person_id')->get();
        $this->info("Has {$protocolLines->count()} lines");
        $notIndentCount = $identService->simpleIdent($protocolLines)->count();
        $this->info('Affected rows count is ' . ($protocolLines->count() - $notIndentCount));
        $time = time() - $startTime;
        $this->info("Time for query: $time");

        $count = 0;
        // Почистим людей у которых 0 протокольных линий
        $persons = $this->listPersonsService->execute(
            new ListPersons(new PersonSearchDto(withoutLines: true))
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
            ->setName('protocol-lines:simple-ident')
            ->setDescription('Simple ident protocol line.')
            ->addArgument(
                'user_id',
                InputArgument::REQUIRED,
                'User Id for impression,'
            )
        ;
    }
}
