<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use Illuminate\Console\Command;

/**
 * Команда для проверки разрядов и их выправления.
 */
class RankValidationCommand extends Command
{
    protected $signature = 'protocol-lines:rank-validation {userId}';

    public function handle(): void
    {
        $userId = (int) $this->argument('userId');

        $this->info('Start by user: ' . $userId);
        foreach (ProtocolLine::all() as $protocolLine) {
            if (Rank::validateRank($protocolLine->rank) || $protocolLine->rank === '') {
                $protocolLine->rank = Rank::getRank($protocolLine->rank) ?: '';
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
