<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupViewAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use App\Models\Cup;
use App\Models\CupEvent;
use App\Repositories\GroupsRepository;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Redirector;

class ShowCupEventGroupAction extends AbstractCupViewAction
{
    private GroupsRepository $groupsRepository;
    private Redirector $redirector;

    public function __construct(
        ViewActionsService $viewService,
        GroupsRepository $groupsRepository,
        Redirector $redirector
    ) {
        parent::__construct($viewService);
        $this->groupsRepository = $groupsRepository;
        $this->redirector = $redirector;
    }

    public function __invoke(string $cup, string $cupEvent, int $groupId): View
    {
        $group = $this->groupsRepository->getGroup($groupId);
        $cup = Cup::find($cup);
        $cupEvent = CupEvent::find($cupEvent);
        $cupType = $cup->cupType();

        if ($group === null) {
            $this->redirector->action(Show404ErrorAction::class);
        }

        $cupEventPoints = $cupType->calculateEvent($cupEvent, $group);

        return $this->view('cup.events.show', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'cupEventPoints' => $cupEventPoints,
            'groupId' => $group->id,
        ]);
    }
}
