<?php

declare(strict_types=1);

namespace App\Domain\CupEvent;

use App\Domain\Auth\Impression;
use App\Domain\Cup\Cup;
use App\Domain\CupEvent\Event\CupEventDisabled;
use App\Domain\CupEvent\Event\CupEventUpdated;
use App\Domain\Event\Event;
use App\Domain\Shared\AggregatedModel;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $cup_id
 * @property int $event_id
 * @property float $points
 * @property boolean $active
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read Cup $cup
 * @property Event $event
 */
class CupEvent extends AggregatedModel
{
    use HasFactory;

    protected $table = 'cup_events';

    protected $casts = [
        'active' => 'boolean',
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    public function disable(Impression $impression): void
    {
        $this->updated = $impression;
        $this->active = false;

        $this->recordThat(new CupEventDisabled($this));
    }

    public function updateData(int $eventId, float $points, Impression $impression): void
    {
        $this->event_id = $eventId;
        $this->points = $points;
        $this->updated = $impression;

        $this->recordThat(new CupEventUpdated($this));
    }

    // TODO REMOVE
    public function cup(): HasOne
    {
        return $this->hasOne(Cup::class, 'id', 'cup_id');
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}
