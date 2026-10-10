<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ExportCupTableAction;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Infrastructure\Sanctum\SanctumUser;
use Database\Seeders\SprintCupLineSeeder;
use DOMDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function count;
use function file_put_contents;
use function rawurlencode;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use const LIBXML_NOERROR;
use const LIBXML_NONET;
use const LIBXML_NOWARNING;

/** @see ExportCupTableAction */
final class ExportCupTableActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_exports_an_xlsx_with_one_sheet_per_group_and_belarusian_columns(): void
    {
        $this->get('/api/v1/cups/101/export')->assertUnauthorized();
        $this->seed(SprintCupLineSeeder::class);
        $this->authenticate();

        $response = $this->get('/api/v1/cups/101/export')->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition');
        $book = $this->readWorkbook((string) $response->getContent());

        $this->assertSame(['М', 'Ж'], $book->getSheetNames());
        $sheet = $book->getSheet(0);
        $this->assertSame(
            ['№', 'Прозвішча, Імя', 'Год', '12.04', 'Ачкі', 'Сярэдняе', 'Месца'],
            $sheet->rangeToArray('A1:G1')[0]
        );
        $this->assertSame('Миссюревич Алексей', $sheet->getCell('B2')->getValue());
        $this->assertTrue($sheet->getStyle('D2')->getFont()->getBold());
        $book->disconnectWorksheets();

        $explicit = $this->get('/api/v1/cups/101/export?format=xlsx')->assertOk();
        $this->assertSame($response->getContent(), $explicit->getContent());
    }

    #[Test]
    public function it_exports_html_with_the_same_columns_and_a_single_group_on_request(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        Cup::query()->whereKey(101)->update(['name' => 'Кубак 2024']);
        $this->authenticate();

        $htmlResponse = $this->get('/api/v1/cups/101/export?format=html&groupId=M_0_')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSeeText('Прозвішча, Імя')
            ->assertSeeText('12.04')
            ->assertSeeText('Сярэдняе');
        $this->assertStringContainsString('filename=export.html', $htmlResponse->headers->get('Content-Disposition'));
        $this->assertStringContainsString("filename*=utf-8''" . rawurlencode('Кубак 2024.html'), $htmlResponse->headers->get('Content-Disposition'));
        $html = $htmlResponse->getContent();
        $document = new DOMDocument();
        $this->assertTrue($document->loadHTML('<?xml encoding="UTF-8"?>' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING));
        $this->assertCount(1, $document->getElementsByTagName('section'));
        $this->assertCount(0, $document->getElementsByTagName('h2'));

        $response = $this->get('/api/v1/cups/101/export?format=xlsx&groupId=M_0_')->assertOk();
        $this->assertStringContainsString("filename*=utf-8''" . rawurlencode('Кубак 2024.xlsx'), $response->headers->get('Content-Disposition'));
        $book = $this->readWorkbook((string) $response->getContent());
        $this->assertSame(['М'], $book->getSheetNames());
        $this->assertSame(
            $book->getActiveSheet()->getCell('B2')->getValue(),
            $document->getElementsByTagName('tbody')->item(0)?->getElementsByTagName('tr')->item(0)?->getElementsByTagName('td')->item(1)?->textContent,
        );
        $book->disconnectWorksheets();
    }

    #[Test]
    public function it_rejects_csv_invalid_groups_and_unknown_cups_without_an_attachment(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        $this->authenticate();

        $this->getJson('/api/v1/cups/101/export?format=csv')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'format')
            ->assertHeaderMissing('Content-Disposition');
        $this->getJson('/api/v1/cups/101/export?format=xlsx&groupId=M_13_')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'groupId');
        $this->getJson('/api/v1/cups/101/export?format=html&groupId=M_12_')
            ->assertBadRequest()
            ->assertJsonPath('errors.0.code', 'cup_group_not_supported');
        $this->getJson('/api/v1/cups/999999/export')
            ->assertNotFound()
            ->assertJsonPath('errors.0.code', 'cup_not_found');
    }

    #[Test]
    public function it_exports_headings_for_an_empty_cup(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        CupEvent::query()->where('cup_id', 101)->update(['active' => false]);
        $this->authenticate();

        $response = $this->get('/api/v1/cups/101/export')->assertOk();
        $book = $this->readWorkbook((string) $response->getContent());
        $this->assertSame(['М', 'Ж'], $book->getSheetNames());
        $this->assertSame('№', $book->getSheet(0)->getCell('A1')->getValue());
        $this->assertSame('Месца', $book->getSheet(0)->getCell('F1')->getValue());
        $this->assertSame(1, $book->getSheet(0)->getHighestRow());
        $book->disconnectWorksheets();
    }

    #[Test]
    public function full_export_reuses_cached_tables(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        $this->authenticate();

        Cache::tags(['cups'])->flush();
        DB::enableQueryLog();
        $this->get('/api/v1/cups/101/export')->assertOk();
        $coldQueries = count(DB::getQueryLog());
        DB::flushQueryLog();
        $this->get('/api/v1/cups/101/export')->assertOk();
        $warmQueries = count(DB::getQueryLog());
        DB::flushQueryLog();
        $this->get('/api/v1/cups/101/export?format=html')->assertOk();
        $htmlQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan($coldQueries, $warmQueries);
        $this->assertSame($warmQueries, $htmlQueries);
    }

    private function authenticate(): void
    {
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));
    }

    private function readWorkbook(string $binary): Spreadsheet
    {
        $path = tempnam(sys_get_temp_dir(), 'bfo-cup-xlsx-');
        $this->assertNotFalse($path);
        file_put_contents($path, $binary);
        try {
            return new Xlsx()->load($path);
        } finally {
            unlink($path);
        }
    }
}
