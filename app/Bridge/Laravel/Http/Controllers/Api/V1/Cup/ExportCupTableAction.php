<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Service\Cup\ExportCupTable;
use App\Application\Service\Cup\ExportCupTableService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Serialization\CupTableCsvSerializer;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class ExportCupTableAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $cupId, ExportCupTableService $service, CupTableCsvSerializer $csv): Response
    {
        $export = $service->execute(new ExportCupTable($cupId));

        return $this->csv(
            $csv->serialize($export),
            $export->cupName . '.csv',
            'cup-' . $export->cupId . '.csv',
        );
    }
}
