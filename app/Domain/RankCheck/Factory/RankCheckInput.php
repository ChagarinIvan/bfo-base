<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Factory;

final readonly class RankCheckInput
{
    public function __construct(
        public int $userId,
        public string $sourcePath,
        public string $content,
        public string $extension,
    ) {
    }
}
