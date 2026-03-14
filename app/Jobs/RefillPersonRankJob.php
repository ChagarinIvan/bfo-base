<?php

namespace App\Jobs;

use App\Services\RankService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefillPersonRankJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
