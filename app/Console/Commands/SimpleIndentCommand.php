<?php

namespace App\Console\Commands;

use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Команда для определения людей с помощью прямого совпадения идентификатора.
 * Запускаем раз в день
 *
 * 1 1 * * * php artisan protocol-lines:simple-ident
 *
 * Class SimpleIndentCommand
 * @package App\Console\Commands
 */
class SimpleIndentCommand extends Command
{
    protected $signature = 'protocol-lines:simple-ident';

    public function handle(): void
    {
        $this->info('Start');
        $startTime = time();
        $protocolLines = ProtocolLine::whereNull('person_id')->get();
        $this->info("Has {$protocolLines->count()} lines");
        $indentCount = IdentService::simpleIdent($protocolLines)->count();
        $this->info("Affected rows count is {$indentCount}");
        $time = time() - $startTime;
        $this->info("Time for query: {$time}");
        //Почистим людей у которых 0 протокольных линий
        DB::delete(DB::raw('DELETE FROM person WHERE id NOT IN (SELECT ANY_VALUE(person_id) FROM protocol_lines WHERE person_id IS NOT NULL GROUP BY person_id);'));
        $this->info("Finish");
    }
}
