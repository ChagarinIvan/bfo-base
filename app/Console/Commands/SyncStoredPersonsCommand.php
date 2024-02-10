<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Integration\OrientBy\OrientByApiClient;
use App\Integration\OrientBy\OrientBySyncService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;

class SyncStoredPersonsCommand extends Command
{
    protected $signature = 'persons:sync:stored';

    public function __construct(
        private readonly OrientBySyncService $service,
        private readonly Filesystem $storage,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Start.');

        for ($i = 2; $i < 4395; $i++) {
            $person = $this->storage->get('/sync/' . $i);
            $person = unserialize($person);
            $this->service->synchronize([$person]);
            $this->storage->delete('/sync/' . $i);
            $this->info('Process '.$i);
        }

        $this->info('Success.');
    }
}
