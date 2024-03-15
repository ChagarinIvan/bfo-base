<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Models\ProtocolLine;
use App\Services\ProtocolLineIdentService;
use Illuminate\Console\Command;
use function time;

/**
 * Команда для добавленія в очередь на определение всех не опознанных людей.
 * Запускаем раз в месяц
 */
class StartBigIdentCommand extends Command
{
    protected $signature = 'protocol-lines:big-ident';

    public function handle(ProtocolLineIdentService $service): void
    {
        $this->info('Start');
        $startTime = time();
        $protocolLines = ProtocolLine::whereNull('person_id')->get();
        $this->info("Has {$protocolLines->count()} lines");
        $service->pushIdentLines($protocolLines->pluck('prepared_line'));
        $time = time() - $startTime;
        $this->info("Time for query: {$time}");
        $this->info("Finish");
    }
}
