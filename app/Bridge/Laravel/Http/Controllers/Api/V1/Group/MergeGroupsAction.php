<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Group;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Group\MergeGroupDto;
use App\Application\Service\Group\MergeGroups;
use App\Application\Service\Group\MergeGroupsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

final class MergeGroupsAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $sourceGroupId, MergeGroupDto $payload, MergeGroupsService $service, UserId $userId): Response
    {
        $service->execute(new MergeGroups($sourceGroupId, $payload->targetGroupId, $userId));

        return response()->noContent();
    }
}
