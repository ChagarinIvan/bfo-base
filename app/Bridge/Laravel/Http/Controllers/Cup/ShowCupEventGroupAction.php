<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Dto\Club\ClubSearchDto;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Dto\CupEvent\ViewCupEventPointDto;
use App\Application\Service\Club\ListClubs;
use App\Application\Service\Club\ListClubsService;
use App\Application\Service\Cup\CalculateCupEvent;
use App\Application\Service\Cup\CalculateCupEventService;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Application\Service\Group\Exception\GroupNotFound;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use function array_column;
use function array_filter;
use function array_unique;
use function array_values;

class ShowCupEventGroupAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        string $cupEventId,
        string $groupId,
        CalculateCupEventService $service,
        ListClubsService $clubsService,
    ): View|RedirectResponse {
        try {
            $calculatedCupEvent = $service->execute(new CalculateCupEvent($cupId, $cupEventId, $groupId));
        } catch (CupNotFound|GroupNotFound|CupEventNotFound) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/cup/events/show.blade.php */
        return $this->view('cup.events.show', [
            'calculatedCupEvent' => $calculatedCupEvent,
            'groupId' => $groupId,
            'clubs' => $this->loadClubs($clubsService, $calculatedCupEvent->points),
        ]);
    }

    /**
     * Preload every club referenced by the points in a single query so the view
     * can render club links without per-row lookups.
     *
     * @param ViewCupEventPointDto[] $points
     *
     * @return array<string, ViewClubDto>
     */
    private function loadClubs(ListClubsService $clubsService, array $points): array
    {
        $clubIds = array_values(array_unique(array_filter(array_column($points, 'personClubId'))));

        if ($clubIds === []) {
            return [];
        }

        $clubs = [];
        foreach ($clubsService->execute(new ListClubs(new ClubSearchDto(ids: $clubIds))) as $club) {
            $clubs[$club->id] = $club;
        }

        return $clubs;
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
