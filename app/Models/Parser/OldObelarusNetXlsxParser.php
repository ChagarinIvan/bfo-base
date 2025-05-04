<?php

declare(strict_types=1);

namespace App\Models\Parser;

use App\Domain\Rank\Rank;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use function array_filter;
use function count;
use function explode;
use function file_put_contents;
use function is_numeric;
use function mb_convert_case;
use function mb_strtolower;
use function preg_match;
use function str_contains;
use function str_replace;
use function strlen;
use function strtolower;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function ucwords;

class OldObelarusNetXlsxParser extends AbstractParser
{
    public function parse(string $file): Collection
    {
        $fileName = tempnam(sys_get_temp_dir(), 'TMP_');
        file_put_contents($fileName, $file);
        $xlsx = new Xlsx();
        $spreadsheet = $xlsx->load($fileName);
        $sheet = $spreadsheet->getActiveSheet();

        $linesList = new Collection();
        $lines = $sheet->toArray();

        $group = '';
        $distancePoints = 0;
        $distanceLength = 0;

        foreach ($lines as $line) {
            if (empty($line[0])) {
                continue;
            }

            if ($line[0] === 'G') {
                $group = $line[1];
                $distancePoints = $line[3];
                $distanceLength = $line[2];

                continue;
            }

            $protocolLine = [
                'serial_number' => (int)$line[0],
                'runner_number' => (int)$line[4],
                'rank' => Rank::validateRank($line[7]) ? $line[7] : null,
                'complete_rank' => Rank::validateRank($line[8]) ? $line[8] : null,
                'club' => mb_convert_case(mb_strtolower($line[3]), MB_CASE_TITLE, "UTF-8"),
                'place' => is_numeric($line[6]) ? (int)$line[6] : null,
                'points' => is_numeric($line[9]) ? (int)$line[9] : 0,
                'time' => str_contains($line[5], ':') ? Carbon::createFromTimeString(str_replace('.', ':', $line[5])) : null,
                'lastname' => mb_convert_case(mb_strtolower(explode(' ', $line[1])[0]), MB_CASE_TITLE, "UTF-8"),
                'firstname' => mb_convert_case(mb_strtolower(explode(' ', $line[1])[1] ?? ''), MB_CASE_TITLE, "UTF-8"),
                'year' => (int)(strlen($line[2]) === 2 ? '19' . $line[2] : $line[2]),
                'group' => $group,
                'distance' => [
                    'length' => $distanceLength,
                    'points' => $distancePoints,
                ],
            ];

            $linesList->push($protocolLine);
        }

        return $linesList;
    }

    public function check(string $file, string $extension): bool
    {
        if (!str_contains($extension, 'openxmlformats')) {
            return false;
        }

        $fileName = tempnam(sys_get_temp_dir(), 'TMP_');
        file_put_contents($fileName, $file);
        $xlsx = new Xlsx();
        $spreadsheet = $xlsx->load($fileName);
        $sheet = $spreadsheet->getActiveSheet();
        $lines = $sheet->toArray();

        return $lines[0][0] === 'G';
    }
}
