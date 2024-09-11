<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Cup\CupEvent\ViewCupEventDto;
use App\Application\Dto\Cup\CupEvent\ViewCupEventPointDto;
use App\Application\Dto\Cup\ViewCalculatedCupEventDto;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Dto\Cup\ViewCupGroupDto;
use App\Application\Dto\Event\EventAssembler;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Event\EventRepository;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use function array_map;
use function sprintf;

final readonly class RankAssembler
{
    public function toViewRankDto(Rank $rank): ViewRankDto
    {
        return new ViewRankDto(
            id: (string) $rank->id,
            rank: $rank->rank,
            personId: (string) $rank->person_id,
        );
    }
}
