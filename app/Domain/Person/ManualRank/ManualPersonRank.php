<?php

declare(strict_types=1);

namespace App\Domain\Person\ManualRank;

use App\Domain\Auth\Impression;
use App\Domain\Person\ManualRank\Event\ManualPersonRankAdded;
use App\Domain\Person\ManualRank\Event\ManualPersonRankDisabled;
use App\Domain\Person\RankFact;
use App\Domain\Rank\Rank;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;

/**
 * @property string $id
 * @property int $person_id
 * @property Rank $rank
 * @property Carbon $achieved_on
 * @property Carbon $activated_on
 * @property bool $active
 * @property Impression $created
 * @property Impression $updated
 */
#[Table(name: 'manual_person_ranks', keyType: 'string', incrementing: false)]
#[WithoutTimestamps]
class ManualPersonRank extends AggregatedModel
{
    public function create(): void
    {
        $this->recordThat(new ManualPersonRankAdded($this->id, $this->person_id));

        $this->save();
    }

    public function disable(Impression $impression): void
    {
        if (!$this->active) {
            return;
        }
        $this->active = false;
        $this->updated = $impression;
        $this->recordThat(new ManualPersonRankDisabled($this->id, $this->person_id));
    }

    public function fact(): RankFact
    {
        return RankFact::fromManualRank($this->id, $this->rank, $this->achieved_on, $this->activated_on);
    }

    protected function casts(): array
    {
        return [
            'person_id' => 'integer',
            'rank' => Rank::class,
            'achieved_on' => 'date',
            'activated_on' => 'date',
            'active' => 'boolean',
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }
}
