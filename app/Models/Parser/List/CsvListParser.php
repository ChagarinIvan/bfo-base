<?php

namespace App\Models\Parser\List;

use App\Models\Parser\ParserInterface;
use Illuminate\Support\Collection;

class CsvListParser implements ParserInterface
{
    public function parse(string $file, bool $needConvert = true): Collection
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

        foreach ($result as $index => $line) {
            if ($index === 0) {
                $values = explode(';', $line);

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
            } elseif (empty($line)) {
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
        return false;
    }
}
