<?php

declare(strict_types=1);

namespace Tests\Infrastructure\RankCheck;

use App\Domain\RankCheck\StandardRankListParser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function mb_convert_encoding;

final class StandardRankListParserTest extends TestCase
{
    #[Test]
    public function splits_name_into_lastname_and_firstname(): void
    {
        $content = "Группа;ФИО;Клуб;Разряд;Номер;Год\r\n"
            . "М18;Иванов Иван Петрович;Клуб;I;12;2005\r\n";

        $items = new StandardRankListParser()->parse(
            mb_convert_encoding($content, 'windows-1251', 'utf-8'),
            'csv',
        );
        $item = $items[0] ?? null;

        $this->assertNotNull($item);
        $this->assertSame('Иванов Иван Петрович', $item->name);
        $this->assertSame('Иванов', $item->lastname);
        $this->assertSame('Иван Петрович', $item->firstname);
    }
}
