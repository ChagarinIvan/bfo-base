<?php

namespace App\Console\Commands;

use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Console\Command;

class SimpleIndentCommand extends Command
{
    protected $signature = 'protocol-lines:simple-ident';

    public function handle(): void
    {
        $this->info('Start');
        $startTime = time();
        $protocolLines = ProtocolLine::whereNull('person_id')->get();
        $this->info("Has {$protocolLines->count()} lines");
        $indentCount = (new IdentService())->simpleIdent($protocolLines);
        $this->info("Affected rows count is {$indentCount}");
        $time = time() - $startTime;
        $this->info("Time for query: {$time}");
        $this->info("Finish");
    }
}
