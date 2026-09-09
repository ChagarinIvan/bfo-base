<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Auth;

use App\Application\Dto\Auth\RegistrationDto;
use App\Application\Service\Auth\SendRegistrationInvitation;
use App\Application\Service\Auth\SendRegistrationInvitationService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class SendRegistrationInvitationAction extends BaseController
{
    use ApiAction;

    public function __invoke(RegistrationDto $registration, SendRegistrationInvitationService $service): Response
    {
        $service->execute(new SendRegistrationInvitation($registration->email));

        return response()->noContent();
    }
}
