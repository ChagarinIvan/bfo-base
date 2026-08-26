<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('rank:activate')]
class FixRankCommand extends Command
{
    public function handle(): void
    {
        foreach (ProtocolLine::cursor() as $line) {
            $line->activate_rank = $line->event->date;
            $line->save();
            $this->info((string) $line->id);
        }
    }
}
