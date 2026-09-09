<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\ProtocolLine;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\ProtocolLine\SetProtocolLinePersonDto;
use App\Application\Service\ProtocolLine\AssignPersonToProtocolLine;
use App\Application\Service\ProtocolLine\AssignPersonToProtocolLineService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class AssignPersonToProtocolLineAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $protocolLineId, SetProtocolLinePersonDto $person, AssignPersonToProtocolLineService $service, UserId $userId): Response
    {
        $service->execute(new AssignPersonToProtocolLine($protocolLineId, $person->personId, $userId));

        return response()->noContent();
    }
}
