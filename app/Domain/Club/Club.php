<?php

declare(strict_types=1);

namespace App\Domain\Club;

use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $normalize_name
 * @property bool $active
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read  Person[]|Collection $persons
 */
class Club extends Model
{
    use HasFactory;

    protected $table = 'club';

    protected $casts = [
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function disable(Impression $impression): void
    {
        $this->updated = $impression;
        $this->active = false;
    }
}
