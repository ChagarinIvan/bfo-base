<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableRequestDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final readonly class CupTableExportResponseAssembler
{
    public function __construct(
        private CupTableCsvSerializer $csv,
        private CupTableHtmlSerializer $html,
    ) {
    }

    public function toResponse(ExportCupTableDto $export, ExportCupTableRequestDto $dto): Response
    {
        if ($dto->isHtml()) {
            $contents = $this->html->serialize($export);
            $contentType = 'text/html; charset=UTF-8';
        } else {
            $contents = $this->csv->serialize($export);
            $contentType = 'text/csv; charset=UTF-8';
        }

        $response = new Response($contents, Response::HTTP_OK, ['Content-Type' => $contentType]);

        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $export->cupName . '.' . $dto->format,
            'cup-' . $export->cupId . '.' . $dto->format,
        ));

        return $response;
    }
}
