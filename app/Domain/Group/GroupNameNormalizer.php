<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Shared\SymbolNormalizer;
use function mb_strtolower;
use function preg_replace;
use function trim;

final readonly class GroupNameNormalizer
{
    public function __construct(private SymbolNormalizer $symbolNormalizer)
    {
    }

    public function normalize(string $name): string
    {
        return $this->symbolNormalizer->normalize((string) preg_replace('#\s+#', ' ', mb_strtolower(trim($name))));
    }
}
