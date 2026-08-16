<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\RankService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use function sprintf;

class RefillPersonRankJob implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * Максимальное время выполнения джоба (сек).
     * Пересчёт рангов тяжёлый — даём запас, чтобы воркер не убивал его по таймауту.
     */
    public int $timeout = 600;

    /**
     * Сколько раз пытаться выполнить джоб перед тем, как отправить его в failed_jobs.
     */
    public int $tries = 3;

    /**
     * Пауза между повторными попытками (сек).
     */
    public array $backoff = [60, 180];

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $personId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(RankService $rankService): void
    {
        Log::info(sprintf('Start fill person "%d" ranks.', $this->personId));
        $rankService->reFillRanksForPerson($this->personId);
    }
}
