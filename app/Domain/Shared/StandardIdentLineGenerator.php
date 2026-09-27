<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use function implode;
use function mb_strtolower;

final readonly class StandardIdentLineGenerator implements IdentLineGenerator
{
    public function __construct(private NameNormalizer $normalizer)
    {
    }

    public function generate(string $lastname, string $firstname, ?int $year = null): string
    {
        $line = [
            $this->normalizer->normalize(mb_strtolower($lastname)),
            $this->normalizer->normalize(mb_strtolower($firstname)),
        ];
        if ($year !== null) {
            $line[] = (string) $year;
        }

        return implode('_', $line);
    }
}
