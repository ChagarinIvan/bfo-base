<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property int $cup_id
 * @property int $event_id
 * @property float $points
 *
 * @property-read Cup $cup
 * @property-read Event $event
 *
 * @method static Builder|CupEvent|null find(mixed $ids)
 */
class CupEvent extends Model
{
    public function cup(): HasOne
    {
        return $this->hasOne(Cup::class, 'id', 'cup_id');
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}
