<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Integration\OrientBy\OrientByApiClient;
use App\Integration\OrientBy\OrientBySyncService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;

class SyncPersonCommand extends Command
{
    protected $signature = 'person:sync {user}';

    public function __construct(
        private readonly OrientBySyncService $service,
        private readonly Filesystem $storage,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Start.');
        $id = $this->argument('user');
        $person = $this->storage->get('/sync/' . $id);
        $person = unserialize($person);
        $this->service->synchronize([$person]);
        $this->storage->delete('/sync/' . $id);
        $this->info('Success.');
    }
}
