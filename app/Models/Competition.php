<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Auth\Impression;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon $from
 * @property Carbon $to
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read Collection|Event[] $events
 */
class Competition extends Model
{
    use HasFactory;

    protected $table = 'competitions';

    protected $casts = [
        'from' => 'datetime:Y-m-d',
        'to' => 'datetime:Y-m-d',
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
