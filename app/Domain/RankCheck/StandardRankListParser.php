<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\Rank\Rank;
use App\Models\Parser\ParserFactory;
use function preg_split;
use function trim;

final readonly class StandardRankListParser implements RankListParser
{
    public function parse(string $content, string $extension): array
    {
        return ParserFactory::createListParser($content, $extension)
            ->parse($content)
            ->map($this->toItem(...))
            ->all()
        ;
    }

    /** @param array<string, mixed> $line */
    private function toItem(array $line): RankListItem
    {
        $name = trim((string) ($line['name'] ?? ''));
        [$lastname, $firstname] = $this->nameParts($name);

        return new RankListItem(
            group: $this->nullableString($line['group'] ?? null),
            name: $name,
            lastname: $lastname,
            firstname: $firstname,
            club: $this->nullableString($line['club'] ?? null),
            rank: Rank::fromProtocolValue($this->nullableString($line['rank'] ?? null)) ?? Rank::WithoutRank,
            number: $this->nullableString($line['number'] ?? null),
            year: $this->nullableInt($line['year'] ?? null),
        );
    }

    /** @return array{string, string} */
    private function nameParts(string $name): array
    {
        $parts = preg_split('/\s+/u', $name, 2) ?: [];

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = $this->nullableString($value);

        return $value === null ? null : (int) $value;
    }
}
