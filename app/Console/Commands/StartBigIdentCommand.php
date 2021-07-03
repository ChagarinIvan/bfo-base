<?php

namespace App\Console\Commands;

use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Console\Command;

/**
 * Команда для добавленія в очередь на определение всех не опознанных людей.
 * Запускаем раз в месяц
 *
 * 1 1 1 * * php artisan protocol-lines:simple-ident
 *
 * Class SimpleIndentCommand
 * @package App\Console\Commands
 */
class StartBigIdentCommand extends Command
{
    protected $signature = 'protocol-lines:big-ident';

    public function handle(): void
    {
        $this->info('Start');
        $startTime = time();
        $protocolLines = ProtocolLine::whereNull('person_id')->get();
        $this->info("Has {$protocolLines->count()} lines");
        (new IdentService())->pushIdentLines($protocolLines);
        $time = time() - $startTime;
        $this->info("Time for query: {$time}");
        $this->info("Finish");
    }
}
