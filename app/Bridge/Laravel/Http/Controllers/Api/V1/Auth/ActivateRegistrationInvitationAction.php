<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Auth;

use App\Application\Service\Auth\ActivateRegistrationInvitation;
use App\Application\Service\Auth\ActivateRegistrationInvitationService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class ActivateRegistrationInvitationAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $token, ActivateRegistrationInvitationService $service): Response
    {
        $service->execute(new ActivateRegistrationInvitation($token));

        return response()->noContent();
    }
}
