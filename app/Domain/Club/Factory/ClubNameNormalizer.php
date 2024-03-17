<?php

declare(strict_types=1);

namespace App\Domain\Club\Factory;

use App\Domain\Shared\SymbolNormalizer;
use function mb_strtolower;
use function preg_replace;
use function str_replace;

final readonly class ClubNameNormalizer
{
    private const EDIT_MAP = [
        'ко ' => ['ка ', 'oc '],
        'ксо ' => ['кса '],
        'бгу' => ['бду', 'bsu'],
    ];
    public function __construct(private SymbolNormalizer $symbolNormalizer)
    {
    }

    public function normalize(string $clubName): string
    {
        $clubName = mb_strtolower($clubName);
        $clubName = str_replace(['\'', '"', '«', '»'], '', $clubName);
        $clubName = preg_replace('#\s+#', ' ', $clubName);

        $clubName = $this->symbolNormalizer->normalize($clubName);

        foreach (self::EDIT_MAP as $name => $analogs) {
            $clubName = str_replace($analogs, $name, $clubName);
        }

        return $clubName;
    }
}
