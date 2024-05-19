<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\DisablePerson;
use App\Application\Service\Person\DisablePersonService;
use App\Services\PersonsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class DeletePersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $id,
        PersonsService $service1,
        DisablePersonService $service,
        UserId $userId,
    ): RedirectResponse {
        $service->execute(new DisablePerson($id, $userId));

        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
