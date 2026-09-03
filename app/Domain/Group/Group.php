<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Auth\Impression;
use App\Domain\Distance\Distance;
use App\Domain\Group\Event\GroupCreated;
use App\Domain\Group\Event\GroupDisabled;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $normalize_name
 * @property bool $active
 * @property int $distances_count
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read Distance[]|Collection $distances
 */
#[Table(name: 'groups')]
#[Fillable(['name', 'normalize_name', 'active'])]
class Group extends AggregatedModel
{
    use HasFactory;

    public function distances(): Builder|HasMany
    {
        return $this->hasMany(Distance::class, 'group_id', 'id');
    }

    public function create(): void
    {
        $this->recordThat(new GroupCreated($this));
        $this->save();
    }

    public function updateName(string $name, string $normalizedName, Impression $impression): void
    {
        $this->name = $name;
        $this->normalize_name = $normalizedName;
        $this->updated = $impression;
    }

    public function disable(Impression $impression): void
    {
        $this->active = false;
        $this->updated = $impression;

        $this->recordThat(new GroupDisabled($this));
    }

    protected function casts(): array
    {
        return [
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }
}
