<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Services\RankService;
use Illuminate\Console\Command;

/**
 * Пересоздаем разряды.
 */
final class RecalculatingRanks extends Command
{
    protected $signature = 'protocol-lines:rank-recalculating {--limit=1000} {--offset=0}';
    protected $description = 'Recalculates ranks for protocol lines with optional limit and offset';

    public function handle(RankService $service): void
    {
        $this->info('Start.');

        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');

        $this->info("Processing with limit: $limit, offset: $offset");

        if ($offset === 0)  {
            Rank::truncate();
        }

        $query = ProtocolLine::query()
            ->orderBy('id')
            ->limit($limit)
            ->offset($offset)
        ;

        foreach ($query->cursor() as $index => $protocolLine) {
            $this->info("Process " . ($offset + $index) . ".");
            $service->fillRank($protocolLine);
        }

        $this->info('Finish.');
    }
}
