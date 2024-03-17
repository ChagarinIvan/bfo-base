<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonDto;
use App\Application\Service\Person\AddPerson;
use App\Application\Service\Person\AddPersonService;
use App\Application\Service\Person\Exception\FailedToAddPerson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

class StorePersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        PersonDto $dto,
        AddPersonService $service,
        UserId $userId,
    ): View|RedirectResponse {
        try {
            $person = $service->execute(new AddPerson($dto, $userId));
        } catch (FailedToAddPerson) {
            return $this->redirectToError();
        }

        return $this->redirector->action(ShowPersonAction::class, [$person->id]);
    }
}
