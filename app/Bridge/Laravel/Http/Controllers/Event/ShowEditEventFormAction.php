<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\ViewEvent;
use App\Application\Service\Event\ViewEventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowEditEventFormAction extends BaseController
{
    use EventAction;

    public function __invoke(string $id, ViewEventService $service): View|RedirectResponse
    {
        try {
            $event = $service->execute(new ViewEvent($id));
        } catch (EventNotFound) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/events/edit.blade.php */
        return $this->view('events.edit', compact('event'));
    }
}
