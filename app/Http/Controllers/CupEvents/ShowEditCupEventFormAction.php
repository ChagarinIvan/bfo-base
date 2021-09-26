<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupViewAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use App\Repositories\CupEventsRepository;
use App\Repositories\CupsRepository;
use App\Repositories\EventsRepository;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Redirector;

class ShowEditCupEventFormAction extends AbstractCupViewAction
{
    private CupsRepository $cupsRepository;
    private CupEventsRepository $cupEventsRepository;
    private EventsRepository $eventsRepository;
    private Redirector $redirector;

    public function __construct(
        ViewActionsService  $viewService,
        CupsRepository      $repository,
        CupEventsRepository $cupEventsRepository,
        EventsRepository    $eventsRepository,
        Redirector          $redirector,
    ) {
        parent::__construct($viewService);
        $this->cupsRepository = $repository;
        $this->cupEventsRepository = $cupEventsRepository;
        $this->eventsRepository = $eventsRepository;
        $this->redirector = $redirector;
    }

    public function __invoke(int $cupId, int $cupEventId): View
    {
        $cup = $this->cupsRepository->getCup($cupId);
        $cupEvent = $this->cupEventsRepository->getCupEvent($cupEventId);

        if ($cup === null || $cupEvent === null) {
            $this->redirector->action(Show404ErrorAction::class);
        }

        $events = $this->eventsRepository->getYearEvents($cup->year);

        return $this->view('cup.events.edit', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'events' => $events,
        ]);
    }
}
