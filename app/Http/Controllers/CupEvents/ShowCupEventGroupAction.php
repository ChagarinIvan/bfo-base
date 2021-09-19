<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupViewAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use App\Repositories\CupEventsRepository;
use App\Repositories\CupsRepository;
use App\Repositories\GroupsRepository;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Redirector;

class ShowCupEventGroupAction extends AbstractCupViewAction
{
    private GroupsRepository $groupsRepository;
    private CupsRepository $cupsRepository;
    private CupEventsRepository $cupEventsRepository;
    private Redirector $redirector;

    public function __construct(
        ViewActionsService $viewService,
        GroupsRepository $groupsRepository,
        CupsRepository $cupsRepository,
        CupEventsRepository $cupEventsRepository,
        Redirector $redirector
    ) {
        parent::__construct($viewService);
        $this->groupsRepository = $groupsRepository;
        $this->cupsRepository = $cupsRepository;
        $this->cupEventsRepository = $cupEventsRepository;
        $this->redirector = $redirector;
    }

    public function __invoke(int $cupId, int $cupEventId, int $groupId): View
    {
        $group = $this->groupsRepository->getGroup($groupId);
        $cup = $this->cupsRepository->getCup($cupId);
        $cupEvent = $this->cupEventsRepository->getCupEvent($cupEventId);

        if ($group === null || $cup === null || $cupEvent === null) {
            $this->redirector->action(Show404ErrorAction::class);
        }

        $cupType = $cup->getCupType();
        $cupEventPoints = $cupType->calculateEvent($cupEvent, $group);

        return $this->view('cup.events.show', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'cupEventPoints' => $cupEventPoints,
            'groupId' => $group->id,
        ]);
    }
}
