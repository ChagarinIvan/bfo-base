<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Cup\ExportCupTableRequestDto;
use App\Application\Service\Cup\ExportCupTable;
use App\Application\Service\Cup\ExportCupTableService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Serialization\CupTableExportResponseAssembler;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class ExportCupTableAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $cupId,
        ExportCupTableRequestDto $dto,
        ExportCupTableService $service,
        CupTableExportResponseAssembler $assembler,
    ): Response {
        $export = $service->execute(new ExportCupTable($cupId, $dto->groupId));

        return $assembler->toResponse($export, $dto);
    }
}
