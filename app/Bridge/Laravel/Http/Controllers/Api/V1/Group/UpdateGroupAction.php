<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Group;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Group\GroupDto;
use App\Application\Dto\Group\ViewGroupDto;
use App\Application\Service\Group\UpdateGroupInfo;
use App\Application\Service\Group\UpdateGroupInfoService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdateGroupAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $groupId, GroupDto $group, UpdateGroupInfoService $service, UserId $userId): ViewGroupDto
    {
        return $service->execute(new UpdateGroupInfo($groupId, $group, $userId));
    }
}
