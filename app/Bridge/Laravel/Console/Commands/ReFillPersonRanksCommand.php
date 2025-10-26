<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Domain\Person\Person;
use App\Services\RankService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

final class ReFillPersonRanksCommand extends Command
{
    protected $signature = 'ranks:person:re-fill';

    public function __construct(
        private readonly RankService $service,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Start');
        $personId = $this->argument('person_id');
        if ($personId) {
            $this->service->reFillRanksForPerson((int) $personId);
        } else {
            $persons = Person::where('active', true)->cursor();
            foreach ($persons as $person) {
                $this->service->reFillRanksForPerson($person->id);
                $this->info("Person {$person->id} refilld.");
            }
        }
        $this->info("Finish");
    }

    protected function configure(): void
    {
        $this
            ->setName('ranks:person:re-fill')
            ->addArgument(
                'person_id',
                InputArgument::OPTIONAL,
                'Person Id.'
            )
        ;
    }
}
