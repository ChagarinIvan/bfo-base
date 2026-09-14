<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event\EventProtocolCreated;
use App\Domain\Event\Event\EventProtocolParsingStarted;
use App\Domain\Event\Event\EventProtocolStatusChanged;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use function count;
use function in_array;

/**
 * An immutable processing run of one uploaded protocol.
 *
 * @property int $id
 * @property int $event_id
 * @property string $run_token
 * @property EventProtocolStatus $status
 * @property int $total_lines
 * @property int $identified_lines
 * @property list<int> $identified_line_ids
 * @property string|null $rank_batch_id
 * @property int $rank_jobs_total
 * @property int $rank_jobs_completed
 * @property list<int> $completed_rank_person_ids
 *
 * @property Impression $created
 * @property Impression $updated
 */
#[Fillable([
    'event_id', 'run_token', 'status', 'total_lines', 'identified_lines',
    'identified_line_ids', 'rank_batch_id', 'completed_rank_person_ids',
])]
#[Table(name: 'event_protocols')]
final class EventProtocol extends AggregatedModel
{
    public static function queue(int $eventId, string $runToken): self
    {
        $protocol = new self;
        $protocol->event_id = $eventId;
        $protocol->run_token = $runToken;
        $protocol->status = EventProtocolStatus::QUEUED;
        $protocol->total_lines = 0;
        $protocol->identified_lines = 0;
        $protocol->identified_line_ids = [];
        $protocol->completed_rank_person_ids = [];
        $protocol->recordThat(new EventProtocolStatusChanged($protocol));

        return $protocol;
    }

    public function startParsing(Impression $impression): bool
    {
        if ($this->status !== EventProtocolStatus::QUEUED) {
            return false;
        }

        $this->updated = $impression;

        $this->transitionTo(EventProtocolStatus::PARSING);
        $this->recordThat(new EventProtocolParsingStarted($this));

        return true;
    }

    public function create(): void
    {
        $this->recordThat(new EventProtocolCreated($this));
        $this->save();
    }

    public function startIdentifying(int $totalLines, Impression $impression): void
    {
        if ($this->status->isTerminal()) {
            return;
        }

        $this->total_lines = $totalLines;
        $this->updated = $impression;
        $this->transitionTo(EventProtocolStatus::IDENTIFYING);
    }

    public function recordIdentifiedLine(int $lineId, Impression $impression): bool
    {
        if ($this->status->isTerminal() || in_array($lineId, $this->identified_line_ids, true)) {
            return false;
        }

        $this->identified_line_ids = [...$this->identified_line_ids, $lineId];
        $this->identified_lines = count($this->identified_line_ids);
        $this->updated = $impression;

        $this->recordThat(new EventProtocolStatusChanged($this));

        return true;
    }

    public function startRankRebuild(string $batchId, int $jobsTotal, Impression $impression): bool
    {
        if ($this->status->isTerminal() || $this->identified_lines !== $this->total_lines || $this->rank_batch_id !== null) {
            return false;
        }

        $this->rank_batch_id = $batchId;
        $this->rank_jobs_total = $jobsTotal;
        $this->rank_jobs_completed = 0;
        $this->completed_rank_person_ids = [];
        $this->updated = $impression;
        $this->transitionTo(EventProtocolStatus::REBUILDING_RANKS);

        if ($jobsTotal === 0) {
            $this->transitionTo(EventProtocolStatus::READY);
        }

        return true;
    }

    public function completeRankJob(string $batchId, int $personId, Impression $impression): bool
    {
        if ($this->status !== EventProtocolStatus::REBUILDING_RANKS || $this->rank_batch_id !== $batchId) {
            return false;
        }

        if ($this->rank_jobs_completed >= $this->rank_jobs_total || in_array($personId, $this->completed_rank_person_ids, true)) {
            return false;
        }

        $this->completed_rank_person_ids = [...$this->completed_rank_person_ids, $personId];
        $this->rank_jobs_completed++;
        $this->updated = $impression;

        if ($this->rank_jobs_completed < $this->rank_jobs_total) {
            $this->recordThat(new EventProtocolStatusChanged($this));

            return true;
        }

        $this->transitionTo(EventProtocolStatus::READY);

        return true;
    }

    public function fail(Impression $impression): void
    {
        if (!$this->status->isTerminal()) {
            $this->updated = $impression;
            $this->transitionTo(EventProtocolStatus::FAILED);
        }
    }

    protected function casts(): array
    {
        return [
            'status' => EventProtocolStatus::class,
            'identified_line_ids' => 'array',
            'completed_rank_person_ids' => 'array',
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }

    private function transitionTo(EventProtocolStatus $status): void
    {
        if ($this->status === $status) {
            return;
        }

        $this->status = $status;
        $this->recordThat(new EventProtocolStatusChanged($this));
    }
}
