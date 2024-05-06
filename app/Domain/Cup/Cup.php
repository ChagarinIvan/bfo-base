<?php

declare(strict_types=1);

namespace App\Domain\Cup;

use App\Domain\Auth\Impression;
use App\Domain\Cup\Event\CupCreated;
use App\Domain\Cup\Event\CupDisabled;
use App\Domain\Cup\Event\CupUpdated;
use App\Domain\Cup\Factory\CupInput;
use App\Domain\CupEvent\CupEvent;
use App\Domain\Shared\AggregatedModel;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use App\Models\Year;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 *
 * @property string $name
 * @property int $events_count
 * @property Year $year
 * @property CupType $type
 * @property boolean $visible
 * @property null|array $result
 * @property boolean $active
 *
 * @property Impression $created
 * @property Impression $updated
 */
class Cup extends AggregatedModel
{
    use HasFactory;

    protected $table = 'cups';

    protected $casts = [
        'result' => 'array',
        'type' => CupType::class,
        'year' => Year::class,
        'active' => 'boolean',
        'visible' => 'boolean',
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    public function disable(Impression $impression): void
    {
        $this->updated = $impression;
        $this->active = false;

        $this->recordThat(new CupDisabled($this));
    }

    public function updateData(CupInput $input, Impression $impression): void
    {
        $this->name = $input->info->name;
        $this->events_count = $input->info->eventsCount;
        $this->year = $input->info->year;
        $this->type = $input->info->type;
        $this->visible = $input->visible;
        $this->updated = $impression;

        $this->recordThat(new CupUpdated($this));
    }

    public function create(): void
    {
        $this->recordThat(new CupCreated($this));

        $this->save();
    }

    public function events(): HasMany|Builder
    {
        return $this->hasMany(CupEvent::class);
    }
}
