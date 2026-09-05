<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person;

use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonApiService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewPersonAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $personId, ViewPersonApiService $service): ViewPersonDto
    {
        return $service->execute(new ViewPerson($personId));
    }
}
