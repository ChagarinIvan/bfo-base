<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Infrastructure\Integration\OrientBy\OrientByApiClient;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use function serialize;

/**
 * Будем синхронизировать членов бфо с главным сайтом федерации.
 * оплаты, разраяды, дни рожденья.
 * Запуск раз в день
 */
#[Signature('persons:sync')]
class SyncPersonsCommand extends Command
{
    public function __construct(private readonly Filesystem $storage)
    {
        parent::__construct();
    }

    public function handle(OrientByApiClient $apiClient): void
    {
        if (!$this->storage->exists('/sync')) {
            $this->storage->makeDirectory('/sync');
        }

        $persons = $apiClient->getPersons();
        foreach ($persons as $index => $person) {
            $this->storage->put('/sync/' . $index, serialize($person));
        }
    }
}
