<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\ProtocolLine;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\ProtocolLine\ExtractPersonFromProtocolLine;
use App\Application\Service\ProtocolLine\ExtractPersonFromProtocolLineService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ExtractPersonAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $protocolLineId, ExtractPersonFromProtocolLineService $service, UserId $userId): ViewPersonDto
    {
        return $service->execute(new ExtractPersonFromProtocolLine($protocolLineId, $userId));
    }
}
