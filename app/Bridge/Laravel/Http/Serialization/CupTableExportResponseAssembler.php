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
        private CupTableXlsxSerializer $xlsx,
        private CupTableHtmlSerializer $html,
    ) {
    }

    public function toResponse(ExportCupTableDto $export, ExportCupTableRequestDto $dto): Response
    {
        if ($dto->isHtml()) {
            $contents = $this->html->serialize($export);
            $contentType = 'text/html; charset=UTF-8';
        } else {
            $contents = $this->xlsx->serialize($export);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        $response = new Response($contents, Response::HTTP_OK, ['Content-Type' => $contentType]);

        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $export->cupName . '.' . $dto->format,
            'export.' . $dto->format,
        ));

        return $response;
    }
}
