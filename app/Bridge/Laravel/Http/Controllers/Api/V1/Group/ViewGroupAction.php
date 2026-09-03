<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Group;

use App\Application\Dto\Group\ViewGroupDto;
use App\Application\Service\Group\ViewGroupService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewGroupAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $groupId, ViewGroupService $service): ViewGroupDto
    {
        return $service->execute((int) $groupId);
    }
}
