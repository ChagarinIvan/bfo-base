<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ProtocolLine;
use Illuminate\Console\Command;

class FixRankCommand extends Command
{
    protected $signature = 'rank:activate';

    public function handle(): void
    {
        foreach (ProtocolLine::cursor() as $line) {
            $line->activate_rank = $line->event->date;
            $line->save();
            $this->info($line->id);
        }
    }
}
