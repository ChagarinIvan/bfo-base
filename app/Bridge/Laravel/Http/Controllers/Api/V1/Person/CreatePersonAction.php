<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonDto;
use App\Application\Dto\Person\PersonInfoDto;
use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\Person\AddPerson;
use App\Application\Service\Person\AddPersonService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(201)]
final class CreatePersonAction extends BaseController
{
    use ApiAction;

    public function __invoke(PersonInfoDto $info, AddPersonService $service, UserId $userId): ViewPersonDto
    {
        $person = new PersonDto();
        $person->info = $info;

        return $service->execute(new AddPerson($person, $userId));
    }
}
