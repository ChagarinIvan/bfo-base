<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Infrastracture\Integration\OrientBy\OrientByPersonDto;
use App\Infrastracture\Integration\OrientBy\OrientBySyncService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;
use function unserialize;

class SyncStoredPersonsCommand extends Command
{
    protected $signature = 'persons:sync:stored';
    private LoggerInterface $logger;

    public function __construct(
        private readonly OrientBySyncService $service,
        private readonly Filesystem $storage,
        LogManager $loggerManager,
    ) {
        parent::__construct();
        $this->logger = $loggerManager->channel('sync');
    }

    public function handle(): void
    {
        $this->logger->info('Start.');
        $userId = (int) $this->argument('user_id');

        try {
            for ($i = 0; $i < 6000; $i++) {
                $path = '/sync/' . $i;
                $person = $this->storage->get($path);
                if (!$person) {
                    continue;
                }

                /** @var OrientByPersonDto $person */
                $person = unserialize($person);
                $this->logger->info('Process ' . $person->name);
                $this->service->synchronize([$person], new UserId($userId));
                $this->logger->info('Delete ' . $path);
                $this->storage->delete($path);
            }
        } catch (Throwable $e) {
            dd($e);
            $this->logger->error($e->getMessage());
        }

        $this->logger->info('Success.');
    }

    protected function configure(): void
    {
        $this
            ->setName('persons:sync:stored')
            ->setDescription('Sync already stored orient by persons,')
            ->addArgument(
                'user_id',
                InputArgument::REQUIRED,
                'User Id for impression,'
            );
    }
}
