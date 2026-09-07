<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonInfoDto;
use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\Person\UpdatePersonInfo;
use App\Application\Service\Person\UpdatePersonInfoService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdatePersonAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $personId,
        PersonInfoDto $person,
        UpdatePersonInfoService $service,
        UserId $userId,
    ): ViewPersonDto {
        return $service->execute(new UpdatePersonInfo($personId, $person, $userId));
    }
}
