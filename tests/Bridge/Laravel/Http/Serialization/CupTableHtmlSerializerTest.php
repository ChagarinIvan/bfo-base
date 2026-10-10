<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableSectionDto;
use App\Application\Dto\Cup\ExportCupTableStageDto;
use App\Application\Dto\Cup\ViewCupTableRowDto;
use App\Application\Dto\Cup\ViewCupTableStageCellDto;
use App\Bridge\Laravel\Http\Serialization\CupTableExportLayout;
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
                new ExportCupTableStageDto(14, '2026-10-09'),
            ], [
                new ViewCupTableRowDto(
                    1,
                    '5',
                    'Іванов <script>alert("x")</script>',
                    2000,
                    'Клуб & сябры',
                    [
                        12 => new ViewCupTableStageCellDto(12, '75', true, '1', '2'),
                        13 => new ViewCupTableStageCellDto(13, '25', false, '3', '4'),
                    ],
                    '75',
                    '75',
                ),
            ]),
            new ExportCupTableSectionDto('Ж35', [], []),
        ]);

        $html = new CupTableHtmlSerializer(new CupTableExportLayout())->serialize($export);

        $this->assertStringContainsString('<!doctype html>', $html);
        $this->assertStringNotContainsString('Кубок &lt;восень&gt;', $html);
        $this->assertStringContainsString('М21 &amp; Ж21', $html);
        $this->assertStringContainsString('<td>Іванов &lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;</td>', $html);
        $this->assertStringNotContainsString('Клуб &amp; сябры', $html);
        $this->assertStringContainsString('<th scope="col" style="text-align:center">07.10</th>', $html);
        $this->assertStringContainsString('<th scope="col" style="text-align:center">Сярэдняе</th>', $html);
        $this->assertStringContainsString('<th scope="col">Прозвішча, Імя</th>', $html);
        $this->assertStringContainsString('<td style="text-align:center">1</td>', $html);
        $this->assertStringContainsString('<td style="text-align:center">2000</td>', $html);
        $this->assertStringContainsString('<td style="text-align:center;font-weight:700">75</td>', $html);
        $this->assertSame(2, substr_count($html, '<td style="text-align:center">75</td>'));
        $this->assertSame(2, substr_count($html, '<td style="text-align:center">1</td>'));
        $this->assertStringContainsString('<td style="text-align:center">25</td>', $html);
        $this->assertStringContainsString('<td style="text-align:center"></td>', $html);
        $this->assertStringNotContainsString('style="text-align:center;font-weight:700">25</td>', $html);
        $this->assertStringNotContainsString('color:', $html);
        $this->assertStringNotContainsString('background-color:', $html);
        $this->assertStringContainsString('<h2>Ж35</h2>', $html);
        $this->assertSame(2, substr_count($html, '<h2>'));
        $this->assertSame(2, substr_count($html, '<table>'));
        $this->assertStringNotContainsString('<script>', $html);
    }

    #[Test]
    public function it_renders_the_group_heading_for_a_single_section(): void
    {
        $export = new ExportCupTableDto('Кубок', 42, [
            new ExportCupTableSectionDto('М21', [], []),
        ]);

        $html = new CupTableHtmlSerializer(new CupTableExportLayout())->serialize($export);

        $this->assertSame(1, substr_count($html, '<section>'));
        $this->assertSame(1, substr_count($html, '<table>'));
        $this->assertStringContainsString('<h2>М21</h2>', $html);
        $this->assertSame(1, substr_count($html, '<h2>'));
        $this->assertStringContainsString('<th scope="col" style="text-align:center">№</th>', $html);
    }
}
