<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Cup\ViewCupTableDto;
use App\Application\Service\Cup\ViewCupTable;
use App\Application\Service\Cup\ViewCupTableService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Cup\Group\CupGroupFactory;
use Illuminate\Routing\Controller as BaseController;

final class ViewCupTableAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $cupId,
        string $groupId,
        ViewCupTableService $service,
    ): ViewCupTableDto {
        return $service->execute(new ViewCupTable($cupId, CupGroupFactory::fromId($groupId)));
    }
}
