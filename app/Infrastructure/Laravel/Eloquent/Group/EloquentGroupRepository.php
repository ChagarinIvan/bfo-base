<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Group;

use App\Domain\Group\Group;
use App\Domain\Group\GroupNameNormalizer;
use App\Domain\Group\GroupRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function array_map;

final class EloquentGroupRepository implements GroupRepository
{
    public function __construct(private readonly GroupNameNormalizer $normalizer)
    {
    }

    public function byId(int $id): ?Group
    {
        return $this->baseQuery()->find($id);
    }

    public function lockById(int $id): ?Group
    {
        return $this->baseQuery()->lockForUpdate()->find($id);
    }

    public function oneByCriteria(Criteria $criteria): ?Group
    {
        $query = Group::query()->where('active', true);

        if ($criteria->hasParam('normalizedName')) {
            $query->where('normalize_name', $criteria->param('normalizedName'));
        }

        return $query->first();
    }

    public function add(Group $group): void
    {
        $group->create();
    }

    public function byCriteria(Criteria $criteria): Collection
    {
        $query = Group::query()->where('groups.active', true)->select('groups.*');

        if ($criteria->hasParam('eventId')) {
            $query->join('distances', 'distances.group_id', '=', 'groups.id')
                ->where('distances.event_id', $criteria->param('eventId'))
                ->distinct();
        }

        if ($criteria->hasParam('names')) {
            $names = array_map(
                fn (string $name): string => $this->normalizer->normalize($name),
                $criteria->param('names'),
            );
            $query->whereIn('normalize_name', $names);
        }

        return $query->get();
    }

    public function paginate(Criteria $criteria): Slice
    {
        $query = $this->baseQuery()->orderByDesc('distances_count')->orderBy('groups.id');
        if ($criteria->hasParam('name')) {
            $normalizedName = $this->normalizer->normalize((string) $criteria->param('name'));
            $query->where('groups.normalize_name', 'LIKE', '%' . $normalizedName . '%');
        }
        if ($criteria->hasParam('excludeId')) {
            $query->where('groups.id', '!=', $criteria->param('excludeId'));
        }

        return new Slice(new EloquentQueryAdapter($query));
    }

    public function all(): Collection
    {
        return Group::query()->where('active', true)->orderBy('name')->orderBy('id')->get();
    }

    public function update(Group $group): void
    {
        $group->save();
    }

    /** @return Builder<Group> */
    private function baseQuery(): Builder
    {
        return Group::query()->where('active', true)->withCount('distances');
    }
}
