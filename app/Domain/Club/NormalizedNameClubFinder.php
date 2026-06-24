<?php

declare(strict_types=1);

namespace App\Domain\Club;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\SymbolNormalizer;
use function mb_strtolower;
use function preg_replace;
use function str_replace;

final readonly class NormalizedNameClubFinder implements ClubFinder
{
    private const EDIT_MAP = [
        'ко ' => ['ка ', 'oc '],
        'ксо ' => ['кса '],
        'бгу' => ['бду', 'bsu'],
    ];

    public static function normalizeName(string $clubName): string
    {
        $clubName = mb_strtolower($clubName);
        $clubName = str_replace(['\'', '"', '«', '»'], '', $clubName);
        $clubName = preg_replace('#\s+#', ' ', $clubName);

        foreach (SymbolNormalizer::SYMBOL_MAP as $symbol => $analogs) {
            $clubName = str_replace($analogs, $symbol, $clubName);
        }

        foreach (self::EDIT_MAP as $name => $analogs) {
            $clubName = str_replace($analogs, $name, $clubName);
        }

        return $clubName;
    }
    public function __construct(private ClubRepository $repository)
    {
    }

    public function findByName(string $clubName): ?Club
    {
        return $this->repository->oneByCriteria(new Criteria(['normalizedName' => self::normalizeName($clubName)]));
    }
}