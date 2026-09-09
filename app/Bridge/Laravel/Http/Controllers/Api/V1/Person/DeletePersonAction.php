<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\DisablePerson;
use App\Application\Service\Person\DisablePersonService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class DeletePersonAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $personId, DisablePersonService $service, UserId $userId): Response
    {
        $service->execute(new DisablePerson($personId, $userId));

        return response()->noContent();
    }
}
