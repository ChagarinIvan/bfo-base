<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\ProtocolLineIdentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use function time;

/**
 * Команда для определения людей с помощью прямого совпадения идентификатора.
 * Запускаем раз в день
 */
class SimpleIndentCommand extends Command
{
    protected $signature = 'protocol-lines:simple-ident';

    public function handle(ProtocolLineIdentService $identService): void
    {
        $this->info('Start');
        $startTime = time();
        $protocolLines = ProtocolLine::whereNull('person_id')->get();
        $this->info("Has {$protocolLines->count()} lines");
        $notIndentCount = $identService->simpleIdent($protocolLines)->count();
        $this->info('Affected rows count is ' . ($protocolLines->count() - $notIndentCount));
        $time = time() - $startTime;
        $this->info("Time for query: $time");

        //Почистим людей у которых 0 протокольных линий
        $expression = DB::raw('SELECT ANY_VALUE(person_id) FROM protocol_lines WHERE person_id IS NOT NULL GROUP BY person_id;');
        Person::whereNotIn('id', $expression)->delete();
        $this->info("Finish");
    }
}
