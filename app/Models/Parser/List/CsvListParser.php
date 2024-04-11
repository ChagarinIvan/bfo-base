<?php

declare(strict_types=1);

namespace App\Models\Parser\List;

use App\Models\Parser\ParserInterface;
use Illuminate\Support\Collection;
use function array_combine;
use function array_map;
use function count;
use function explode;
use function mb_convert_encoding;
use function preg_split;

class CsvListParser implements ParserInterface
{
    public function parse(string $file): Collection
    {
        $list = new Collection();
        $content = mb_convert_encoding($file, 'utf-8', 'windows-1251');
        $result = preg_split('#\r\n#', $content);
        $headers = [
            'group',
            'name',
            'club',
            'rank',
            'number',
            'year',
        ];
        dd($content);
        foreach ($result as $index => $line) {
            if ($index === 0) {
                $values = explode(';', $line);
                $values = array_map('trim', $values);

                if (count($values) === 5) {
                    $headers = [
                        'group',
                        'name',
                        'club',
                        'rank',
                        'year',
                    ];
                }

                continue;
            }

            if (empty($line)) {
                continue;
            }
            $values = explode(';', $line);

            if ($values[1] === 'Вакансия') {
                continue;
            }

            $list->push(array_combine($headers, $values));
        }

        return $list;
    }

    public function check(string $file, string $extension): bool
    {
        return str_contains($extension, 'csv');
    }
}
