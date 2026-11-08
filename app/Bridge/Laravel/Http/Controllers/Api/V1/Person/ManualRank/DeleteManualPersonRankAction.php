<?php

declare(strict_types=1);
namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ManualRank;
use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\ManualRank\DisableManualPersonRank;
use App\Application\Service\Person\ManualRank\DisableManualPersonRankService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
final class DeleteManualPersonRankAction extends BaseController
{
    use ApiAction;
    public function __invoke(int $personId, string $manualRankId, UserId $userId, DisableManualPersonRankService $service): Response
    {
        $service->execute(new DisableManualPersonRank($personId, $manualRankId, $userId));
        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
