<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\Auth\Impression;
use App\Domain\RankCheck\Event\RankCheckCreated;
use App\Domain\RankCheck\Event\RankCheckFailed;
use App\Domain\RankCheck\Event\RankCheckReady;
use App\Domain\RankCheck\Exception\ProcessError;
use App\Domain\RankCheck\Exception\UnableToProcess;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property RankCheckStatus $status
 * @property string $source_path
 * @property string|null $error_message
 * @property Impression $created
 * @property Impression $updated
 */

#[Fillable(['created_by', 'updated_by', 'status', 'source_path', 'error_message', 'created', 'updated'])]
#[Table(name: 'rank_checks')]
class RankCheck extends AggregatedModel
{
    public function create(): void
    {
        $this->recordThat(new RankCheckCreated($this));
        $this->save();
    }

    public function rows(): HasMany
    {
        return $this->hasMany(RankCheckRow::class)->orderBy('position');
    }

    public function process(RankCheckProcessor $processor, Impression $impression): void
    {
        if ($this->status !== RankCheckStatus::Parsing) {
            throw new UnableToProcess();
        }

        try {
            $processor->process($this);
        } catch (ProcessError $exception) {
            $this->markFailed($exception->getMessage(), $impression);

            return;
        }

        $this->markReady($impression);
    }

    public function markFailed(string $message, Impression $impression): void
    {
        if ($this->status !== RankCheckStatus::Parsing) {
            return;
        }

        $this->status = RankCheckStatus::Failed;
        $this->error_message = $message;
        $this->updated = $impression;
        $this->recordThat(new RankCheckFailed($this));
    }

    protected function casts(): array
    {
        return [
            'status' => RankCheckStatus::class,
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }

    private function markReady(Impression $impression): void
    {
        if ($this->status !== RankCheckStatus::Parsing) {
            return;
        }

        $this->status = RankCheckStatus::Ready;
        $this->updated = $impression;

        $this->recordThat(new RankCheckReady($this));
    }
}
