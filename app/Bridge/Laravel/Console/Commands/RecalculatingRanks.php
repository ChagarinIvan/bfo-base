<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Models\ProtocolLine;
use App\Models\Rank;
use App\Services\RankService;
use Illuminate\Console\Command;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;

/**
 * Пересоздаем разряды.
 */
final class RecalculatingRanks extends Command
{
    protected $signature = 'protocol-lines:rank-recalculating';
    private readonly LoggerInterface $logger;

    public function __construct(
        LogManager $loggerManager,
    ) {
        parent::__construct();
        $this->logger = $loggerManager->channel('ranks');
    }
    public function handle(RankService $service): void
    {
        $this->logger->info('Start.');

        Rank::truncate();
        foreach (ProtocolLine::cursor() as $index => $protocolLine) {
            $this->logger->info("Process $index.");
            $service->fillRank($protocolLine);
        }

        $this->logger->info('Finish.');
    }
}
