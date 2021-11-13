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
        foreach ($result as $index => $line) {
            if ($index === 0 || empty($line)) {
                continue;
            }
            $values = explode(';', $line);

            if ($values[1] === 'Вакансия') {
                continue;
            }

            $list->push([
                'group' => $values[0],
                'name' => $values[1],
                'club' => $values[2],
                'rank' => $values[3],
                'number' => $values[4],
                'year' => $values[5],
            ]);
        }

        return $list;
    }

    public function check(string $file): bool
    {
        return true;
    }
}
