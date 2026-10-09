<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Domain\Person\PersonRankExportRow;
use RuntimeException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function fopen;
use function fputcsv;
use function fwrite;
use function is_resource;

final readonly class PersonRanksCsvSerializer
{
    /** @param iterable<PersonRankExportRow> $rows */
    public function toResponse(iterable $rows): StreamedResponse
    {
        $response = new StreamedResponse(static function () use ($rows): void {
            $stream = fopen('php://output', 'wb');
            if (!is_resource($stream)) {
                throw new RuntimeException('Cannot open CSV output');
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['lastname', 'firstname', 'birthday', 'rank'], ';', '"', '', "\r\n");
            foreach ($rows as $row) {
                fputcsv($stream, [$row->lastname, $row->firstname, $row->birthYear, $row->rank], ';', '"', '', "\r\n");
            }
        }, 200, ['Content-Type' => 'text/csv; charset=UTF-8']);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'persons-ranks.csv',
        ));

        return $response;
    }
}
