<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\CupEvent\CupEvent;
use App\Domain\Event\Event\EventCreated;
use App\Domain\Event\Event\EventDisabled;
use App\Domain\Event\Event\EventUpdated;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\AggregatedModel;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use App\Models\Distance;
use App\Models\Flag;
use App\Models\Rank;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon $date
 * @property int $competition_id
 * @property string $file
 * @property bool $active
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read Competition|null $competition
 * @property-read Collection|ProtocolLine[] $protocolLines
 * @property-read Collection|Distance[] $distances
 * @property-read Collection|CupEvent[] $cups
 * @property-read Collection|Flag[] $flags
 */
class Event extends AggregatedModel
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'name', 'description', 'date'
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    public function competition(): HasOne
    {
        return $this->hasOne(Competition::class, 'id', 'competition_id');
    }

    public function protocolLines(): HasManyThrough
    {
        return $this->hasManyThrough(ProtocolLine::class, Distance::class, 'event_id', 'distance_id', 'id', 'id');
    }

    public function distances(): HasMany|Builder
    {
        return $this->hasMany(Distance::class, 'event_id', 'id');
    }

    public function cups(): HasMany
    {
        return $this->hasMany(CupEvent::class);
    }

    public function flags(): BelongsToMany
    {
        return $this->belongsToMany(Flag::class, 'event_flags');
    }

    public function ranks(): HasMany|Builder
    {
        return $this->hasMany(Rank::class);
    }

    public function disable(Impression $impression): void
    {
        $this->updated = $impression;
        $this->active = false;

        $this->recordThat(new EventDisabled($this));
    }

    public function updateData(ProtocolUpdater $updater, UpdateInput $input, Impression $impression): void
    {
        $this->name = $input->info->name;
        $this->description = $input->info->description;
        $this->date = $input->info->date;
        $this->file = $input->protocol ? $updater->update($this, $input->protocol) : $this->file;
        $this->updated = $impression;

        $this->recordThat(new EventUpdated($this, (bool) $input->protocol));
    }

    public function create(): void
    {
        $this->recordThat(new EventCreated($this));

        $this->save();
    }
}
