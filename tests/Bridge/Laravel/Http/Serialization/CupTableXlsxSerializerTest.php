<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Dto\Cup\ExportCupTableSectionDto;
use App\Application\Dto\Cup\ExportCupTableStageDto;
use App\Application\Dto\Cup\ViewCupTableRowDto;
use App\Application\Dto\Cup\ViewCupTableStageCellDto;
use App\Bridge\Laravel\Http\Serialization\CupTableExportLayout;
use App\Bridge\Laravel\Http\Serialization\CupTableXlsxSerializer;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function file_put_contents;
use function mb_strlen;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

final class CupTableXlsxSerializerTest extends TestCase
{
    #[Test]
    public function it_keeps_user_text_literal_and_bolds_only_counted_stage_cells(): void
    {
        $name = 'Вельмі/доўгая:назва*групы?2026';
        $stages = [new ExportCupTableStageDto(1, '2026-03-28'), new ExportCupTableStageDto(2, '2026-04-19')];
        $row = new ViewCupTableRowDto(4, '7', '=SUM(A1:A2)', 2000, 'Клуб', [
            1 => new ViewCupTableStageCellDto(1, '25', true, '1', '1'),
            2 => new ViewCupTableStageCellDto(2, '10', false, '1', '2'),
        ], '25', '25');
        $dto = new ExportCupTableDto('Кубак', 42, [
            new ExportCupTableSectionDto($name, $stages, [$row]),
            new ExportCupTableSectionDto($name, [], []),
        ]);

        $binary = new CupTableXlsxSerializer(new CupTableExportLayout())->serialize($dto);
        $path = tempnam(sys_get_temp_dir(), 'bfo-xlsx-');
        $this->assertNotFalse($path);
        file_put_contents($path, $binary);
        try {
            $book = new Xlsx()->load($path);
            $names = $book->getSheetNames();
            $this->assertCount(2, $names);
            $this->assertNotSame($names[0], $names[1]);
            $this->assertLessThanOrEqual(31, mb_strlen($names[0]));
            $sheet = $book->getSheet(0);
            $this->assertSame('28.03', $sheet->getCell('D1')->getValue());
            $this->assertSame('19.04', $sheet->getCell('E1')->getValue());
            $this->assertSame('=SUM(A1:A2)', $sheet->getCell('B2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B2')->getDataType());
            $this->assertTrue($sheet->getStyle('D2')->getFont()->getBold());
            $this->assertFalse($sheet->getStyle('E2')->getFont()->getBold());
            $this->assertEqualsWithDelta(4.0, $sheet->getCell('H2')->getValue(), PHP_FLOAT_EPSILON);
            $book->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }
}
