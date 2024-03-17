<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonInfoDto;
use App\Application\Service\Person\UpdatePersonInfo;
use App\Application\Service\Person\UpdatePersonInfoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class UpdatePersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $personId,
        PersonInfoDto $info,
        UpdatePersonInfoService $service,
        UserId $userId,
    ): RedirectResponse {
        $person = $service->execute(new UpdatePersonInfo($personId, $info, $userId));

        return $this->redirector->action(ShowPersonAction::class, [$person->id]);
    }
}
