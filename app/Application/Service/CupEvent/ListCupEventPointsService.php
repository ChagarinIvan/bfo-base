<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\CupEvent\ViewCupEventPointDto;
use App\Application\Service\Cup\CalculateCupEvent;
use App\Application\Service\Cup\CalculateCupEventService;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Application\Service\Group\Exception\GroupNotFound;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Shared\Pagination\ArraySliceAdapter;
use App\Domain\Shared\Pagination\Slice;
use function array_filter;
use function array_map;
use function mb_strtolower;
use function str_contains;

final readonly class ListCupEventPointsService
{
    public function __construct(
        private CupRepository $cups,
        private CupEventRepository $cupEvents,
        private CalculateCupEventService $calculator,
    ) {
    }

    /**
     * @throws CupEventNotFound
     * @throws CupNotFound
     * @throws GroupNotFound
     * @return Slice<ViewCupEventPointDto>
     */
    public function execute(ListCupEventPoints $command): Slice
    {
        $cupEvent = $this->cupEvents->byId($command->cupEventId()) ?? throw new CupEventNotFound();
        $cup = $this->cups->byId($cupEvent->cup_id) ?? throw new CupNotFound();
        $search = $command->search();
        $groupId = $search->groupId ?? throw new GroupNotFound();

        if (!array_filter($cup->groups(), static fn (CupGroup $group): bool => $group->id() === $groupId)) {
            throw new GroupNotFound();
        }

        $points = $this->calculator
            ->execute(new CalculateCupEvent((string) $cup->id, (string) $cupEvent->id, $groupId))
            ->points
        ;

        if ($search->name !== null) {
            $name = mb_strtolower($search->name);
            $points = array_filter(
                $points,
                static fn (ViewCupEventPointDto $point): bool => str_contains(mb_strtolower($point->personName), $name),
            );
        }

        return new Slice(new ArraySliceAdapter(array_map(
            static fn (ViewCupEventPointDto $point): ViewCupEventPointDto => $point,
            $points,
        )));
    }
}
