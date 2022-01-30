<?php

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractAction;
use App\Models\Person;
use App\Services\PersonsService;
use App\Services\ViewActionsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class DeletePersonAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        private readonly PersonsService $personsService
    ) {
        parent::__construct($viewService, $redirector);
    }

    public function __invoke(Person $person): RedirectResponse
    {
        $this->personsService->deletePerson($person);
        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
