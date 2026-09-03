<?php

declare(strict_types=1);

namespace App\Models\Parser;

use App\Domain\Group\GroupNameNormalizer;
use App\Domain\Rank\RankNormalizer;
use App\Domain\Shared\SymbolNormalizer;
use Illuminate\Support\Collection;
use function str_contains;

abstract class AbstractParser implements ParserInterface
{
    abstract public function parse(string $file): Collection;

    abstract public function check(string $file, string $extension): bool;

    public function __construct(
        protected Collection $groups,
        protected RankNormalizer $rankNormalizer,
        private readonly GroupNameNormalizer $groupNameNormalizer = new GroupNameNormalizer(new SymbolNormalizer),
    )
    {
    }

    protected function isKnownGroup(string $name): bool
    {
        return $this->groups->containsStrict($this->groupNameNormalizer->normalize($name));
    }

    protected function containsKnownGroup(string $value): bool
    {
        $normalizedValue = $this->groupNameNormalizer->normalize($value);

        return $this->groups->contains(static fn (string $groupName): bool => str_contains($normalizedValue, $groupName));
    }
}
