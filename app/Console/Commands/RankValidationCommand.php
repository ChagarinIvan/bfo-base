<?php

namespace App\Console\Commands;

use App\Models\ProtocolLine;
use App\Models\Rank;
use Illuminate\Console\Command;

/**
 * Команда для проверки разрядов и из выправления.
 *
 * Class RankValidationCommand
 * @package App\Console\Commands
 */
class RankValidationCommand extends Command
{
    protected $signature = 'protocol-lines:rank-validation';

    public function handle(): void
    {
        $this->info('Start');
        foreach (ProtocolLine::all() as $protocolLine) {
            if (Rank::validateRank($protocolLine->rank) || $protocolLine->rank === '') {
                $protocolLine->rank = Rank::getRank($protocolLine->rank) ?? '';
            } else {
                $this->info("no valid rank: {$protocolLine->rank}");
                $protocolLine->rank = '';
            }

            if (
                Rank::validateRank($protocolLine->complete_rank) ||
                $protocolLine->complete_rank === '' ||
                $protocolLine->complete_rank === '-'
            ) {
                $protocolLine->complete_rank = Rank::getRank($protocolLine->complete_rank) ?? '';
            } else {
                $this->info("no valid complete rank: {$protocolLine->complete_rank}");
                $protocolLine->complete_rank = '';
            }
            $protocolLine->save();
        }
        $this->info("Finish");
    }
}
