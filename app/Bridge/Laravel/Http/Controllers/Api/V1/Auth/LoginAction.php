<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Auth;

use App\Application\Dto\Auth\LoginDto;
use App\Application\Dto\Auth\ViewTokenDto;
use App\Application\Service\Auth\Login;
use App\Application\Service\Auth\LoginService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class LoginAction extends BaseController
{
    use ApiAction;

    public function __invoke(LoginDto $login, LoginService $service): ViewTokenDto
    {
        return $service->execute(new Login($login));
    }
}
