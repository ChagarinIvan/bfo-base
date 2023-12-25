<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ProtocolLine;
use App\Models\Rank;
use App\Services\RankService;
use Illuminate\Console\Command;

/**
 * Пересоздаем разряды.
 */
class RecalculatingRanks extends Command
{
    protected $signature = 'protocol-lines:rank-recalculating';

    public function handle(RankService $service): void
    {
        Rank::truncate();
        foreach (ProtocolLine::all() as $protocolLine) {
            $service->fillRank($protocolLine);
        }
        $this->info("Finish");
    }
}
