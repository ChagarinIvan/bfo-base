<?php

declare(strict_types=1);

namespace App\Domain\Club;

use App\Domain\Auth\Impression;
use App\Domain\Club\Event\ClubCreated;
use App\Domain\Club\Event\ClubInfoUpdated;
use App\Domain\Person\Person;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $normalize_name
 * @property bool $active
 * @property int $persons_count
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read  Person[]|Collection $persons
 */
#[Table(name: 'club')]
class Club extends AggregatedModel
{
    use HasFactory;

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function disable(Impression $impression): void
    {
        $this->updated = $impression;
        $this->active = false;
    }

    public function create(): void
    {
        $this->recordThat(new ClubCreated($this));

        $this->save();
    }

    public function updateInfo(ClubInfo $info, Impression $impression): void
    {
        $this->name = $info->name;
        $this->normalize_name = $info->normalizeName;
        $this->updated = $impression;

        $this->recordThat(new ClubInfoUpdated($this));
    }
    protected function casts(): array
    {
        return [
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }
}
