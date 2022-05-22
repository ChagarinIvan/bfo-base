<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Integration\OrientBy\OrientBySyncService;
use Illuminate\Console\Command;

/**
 * Будем синхронизировать членов бфо с главным сайтом федерации.
 * оплаты, разраяды, дни рожденья.
 * Запуск раз в день
 */
class SyncPersonsCommand extends Command
{
    protected $signature = 'persons:sync';

    public function handle(OrientBySyncService $syncService): void
    {
//        $syncService->synchronize();
    }
}
