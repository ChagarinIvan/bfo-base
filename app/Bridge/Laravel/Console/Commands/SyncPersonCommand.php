<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Infrastructure\Integration\OrientBy\OrientByPersonDto;
use App\Infrastructure\Integration\OrientBy\OrientBySyncService;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;

#[Signature('person:sync')]
class SyncPersonCommand extends Command
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly OrientBySyncService $service,
        LogManager $loggerManager,
    ) {
        parent::__construct();
        $this->logger = $loggerManager->channel('sync');
    }

    public function handle(): void
    {
        $this->logger->info('Start.');
        $userId = (int) $this->argument('user_id');
        $person = new OrientByPersonDto(
            name: $this->argument('name'),
            yob: (int) $this->argument('yob'),
            club: $this->argument('club'),
            rank: $this->argument('rank'),
            paid: (bool) $this->argument('paid'),
            paymentDate: $this->argument('paymentDate'),
        );

        $this->service->synchronize([$person], new UserId($userId));
        $this->logger->info('Success.');
    }

    protected function configure(): void
    {
        $this
            ->setName('person:sync')
            ->setDescription('Sync person manually')
            ->addArgument(
                'user_id',
                InputArgument::REQUIRED,
                'User Id for impression,'
            )
            ->addArgument(
                'paid',
                InputArgument::REQUIRED
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED
            )
            ->addArgument(
                'yob',
                InputArgument::OPTIONAL
            )
            ->addArgument(
                'club',
                InputArgument::OPTIONAL
            )
            ->addArgument(
                'rank',
                InputArgument::OPTIONAL
            )
            ->addArgument(
                'paymentDate',
                InputArgument::OPTIONAL
            )
        ;
    }
}
