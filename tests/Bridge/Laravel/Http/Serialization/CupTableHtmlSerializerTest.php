<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableSectionDto;
use App\Application\Dto\Cup\ExportCupTableStageDto;
use App\Application\Dto\Cup\ViewCupTableRowDto;
use App\Application\Dto\Cup\ViewCupTableStageCellDto;
use App\Bridge\Laravel\Http\Serialization\CupTableHtmlSerializer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function substr_count;

final class CupTableHtmlSerializerTest extends TestCase
{
    #[Test]
    public function it_renders_all_sections_and_escapes_user_text(): void
    {
        $export = new ExportCupTableDto('Кубок <восень>', 42, [
            new ExportCupTableSectionDto('М21 & Ж21', [
                new ExportCupTableStageDto(12, '2026-10-07'),
                new ExportCupTableStageDto(13, '2026-10-08'),
            ], [
                new ViewCupTableRowDto(
                    1,
                    '5',
                    'Іванов <script>alert("x")</script>',
                    2000,
                    'Клуб & сябры',
                    [12 => new ViewCupTableStageCellDto(12, '75', true, '1', '2')],
                    '75',
                    '75',
                ),
            ]),
            new ExportCupTableSectionDto('Ж35', [], []),
        ]);

        $html = new CupTableHtmlSerializer()->serialize($export);

        $this->assertStringContainsString('<!doctype html>', $html);
        $this->assertStringContainsString('Кубок &lt;восень&gt;', $html);
        $this->assertStringContainsString('М21 &amp; Ж21', $html);
        $this->assertStringContainsString('Іванов &lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', $html);
        $this->assertStringContainsString('Клуб &amp; сябры', $html);
        $this->assertStringContainsString('<th scope="col">2026-10-07</th>', $html);
        $this->assertStringContainsString('<td>75</td>', $html);
        $this->assertStringContainsString('<td></td>', $html);
        $this->assertStringContainsString('<h2>Ж35</h2>', $html);
        $this->assertSame(2, substr_count($html, '<table>'));
        $this->assertStringNotContainsString('<script>', $html);
    }
}
