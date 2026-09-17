<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

interface RankListParser
{
    /** @return list<RankListItem> */
    public function parse(string $content, string $extension): array;
}
