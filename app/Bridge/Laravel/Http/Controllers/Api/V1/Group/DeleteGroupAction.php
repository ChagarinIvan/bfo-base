<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Group;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Group\DeleteGroup;
use App\Application\Service\Group\DeleteGroupService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

final class DeleteGroupAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $groupId, DeleteGroupService $service, UserId $userId): Response
    {
        $service->execute(new DeleteGroup($groupId, $userId));

        return response()->noContent();
    }
}
