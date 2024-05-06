<?php

declare(strict_types=1);

namespace App\Domain\CupEvent;

use App\Domain\Cup\Cup;
use App\Domain\Event\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $cup_id
 * @property int $event_id
 * @property float $points
 *
 * @property-read Cup $cup
 * @property-read Event $event
 */
class CupEvent extends Model
{
    use HasFactory;

    protected $table = 'cup_events';

    public function cup(): HasOne
    {
        return $this->hasOne(Cup::class, 'id', 'cup_id');
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}
