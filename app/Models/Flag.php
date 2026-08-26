<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Event\Event;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $color
 *
 * @property-read Collection|Event[] $events
 *
 * @method static Builder|Flag find(mixed $ids)
 * @method static Builder|Flag with(mixed $params)
 */
#[Fillable(['name', 'color'])]
#[Table(name: 'flags')]
#[WithoutTimestamps]
class Flag extends Model
{
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_flags');
    }
}
