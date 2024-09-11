<?php

declare(strict_types=1);

namespace App\Domain\Competition;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Event\CompetitionCreated;
use App\Domain\Competition\Event\CompetitionDisabled;
use App\Domain\Event\Event;
use App\Domain\Shared\AggregatedModel;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use Carbon\Carbon;
use Database\Factories\Domain\Competition\CompetitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property Carbon $from
 * @property Carbon $to
 * @property bool $active
 *
 * @property Impression $created
 * @property Impression $updated
 */
class Competition extends AggregatedModel
{
    /** @see CompetitionFactory */
    use HasFactory;

    protected $table = 'competitions';

    protected $casts = [
        'from' => 'datetime:Y-m-d',
        'to' => 'datetime:Y-m-d',
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    public function updateInfo(CompetitionInfo $info, Impression $impression): void
    {
        $this->name = $info->name;
        $this->description = $info->description;
        $this->from = $info->from;
        $this->to = $info->to;

        $this->updated = $impression;
    }

    public function disable(Impression $impression): void
    {
        $this->updated = $impression;
        $this->active = false;

        $this->recordThat(new CompetitionDisabled($this));
    }

    public function create(): void
    {
        $this->recordThat(new CompetitionCreated($this));

        $this->save();
    }
}
