<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Services\PersonsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class DeletePersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(string $personId, PersonsService $service): RedirectResponse
    {
        $service->deletePerson((int) $personId);

        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
