<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableSectionDto;
use App\Application\Dto\Cup\ExportCupTableStageDto;
use App\Application\Dto\Cup\ViewCupTableRowDto;
use App\Application\Dto\Cup\ViewCupTableStageCellDto;
use App\Bridge\Laravel\Http\Serialization\CupTableCsvSerializer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CupTableCsvSerializerTest extends TestCase
{
    #[Test]
    public function it_quotes_special_fields_and_includes_stage_points(): void
    {
        $section = new ExportCupTableSectionDto(
            'М0',
            [new ExportCupTableStageDto(7, '2026-09-26')],
            [new ViewCupTableRowDto(
                1,
                '42',
                'Last; "First"',
                2000,
                "Club\nNorth",
                ['7' => new ViewCupTableStageCellDto(7, '100', true, '3', '4')],
                '100',
                '100',
            )],
        );
        $export = new ExportCupTableDto('Cup', 1, [$section]);

        $csv = new CupTableCsvSerializer()->serialize($export);

        $this->assertStringContainsString("Место;ФИО;Год;Клуб;Очки;2026-09-26\r\n", $csv);
        $this->assertStringContainsString('"Last; ""First"""', $csv);
        $this->assertStringContainsString("\"Club\r\nNorth\"", $csv);
        $this->assertStringContainsString(';100;100', $csv);
    }
}
