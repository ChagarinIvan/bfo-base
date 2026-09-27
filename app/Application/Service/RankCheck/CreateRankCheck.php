<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\RankCheck\RankCheckListDto;

final readonly class CreateRankCheck
{
    public function __construct(
        public RankCheckListDto $list,
        public UserId $userId,
    ) {
    }

    public function content(): string
    {
        return $this->list->list?->getContent() ?? '';
    }

    public function extension(): string
    {
        return $this->list->list?->getClientOriginalExtension() ?? '';
    }
}
