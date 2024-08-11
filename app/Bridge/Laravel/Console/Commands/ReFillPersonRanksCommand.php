<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

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
        $personId = (int) $this->argument('person_id');
        $this->service->reFillRanksForPerson($personId);
        $this->info("Finish");
    }

    protected function configure(): void
    {
        $this
            ->setName('ranks:person:re-fill')
            ->addArgument(
                'person_id',
                InputArgument::REQUIRED,
                'Person Id.'
            )
        ;
    }
}
