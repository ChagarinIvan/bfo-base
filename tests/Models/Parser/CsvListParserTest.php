<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\List\CsvListParser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function mb_convert_encoding;

final class CsvListParserTest extends TestCase
{
    #[Test]
    public function parses_standard_rank_list_and_skips_vacancies(): void
    {
        $content = "Группа;ФИО;Клуб;Разряд;Номер;Год\r\n"
            . "М18;Иванов Иван;Клуб;I;12;2005\r\n"
            . "М18;Вакансия;;;;\r\n"
            . "Ж18;Петрова Анна;Клуб;б/р;;2006\r\n";

        $rows = new CsvListParser()->parse(mb_convert_encoding($content, 'windows-1251', 'utf-8'));

        $this->assertCount(2, $rows);
        $this->assertSame('Иванов Иван', $rows[0]['name']);
        $this->assertSame('12', $rows[0]['number']);
        $this->assertSame('Петрова Анна', $rows[1]['name']);
    }
}
