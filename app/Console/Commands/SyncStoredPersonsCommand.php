<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Integration\OrientBy\OrientByApiClient;
use App\Integration\OrientBy\OrientByPersonDto;
use App\Integration\OrientBy\OrientBySyncService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;

class SyncStoredPersonsCommand extends Command
{
    protected $signature = 'persons:sync:stored';
    private LoggerInterface $logger;

    public function __construct(
        private readonly OrientBySyncService $service,
        private readonly Filesystem $storage,
        private readonly LogManager $loggerManager,
    ) {
        parent::__construct();
        $this->logger = $loggerManager->channel('sync');
    }

    public function handle(): void
    {
        $this->logger->info('Start.');

        try {
            for ($i = 0; $i < 4409; $i++) {
                $path = '/sync/' . $i;
                $person = $this->storage->get($path);
                if (!$person) {
                    continue;
                }

                /** @var OrientByPersonDto $person */
                $person = unserialize($person);
                $this->logger->info('Process ' . $person->name);
                $this->service->synchronize([$person]);
                $this->logger->info('Delete ' . $path);
                $this->storage->delete($path);
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        $this->logger->info('Success.');
    }
}
