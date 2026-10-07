<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ExportCupTableAction;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\GroupMale;
use App\Domain\Distance\Distance;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastructure\Sanctum\SanctumUser;
use Database\Seeders\SprintCupLineSeeder;
use DOMDocument;
use DOMElement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function count;
use function fclose;
use function fgetcsv;
use function fopen;
use function fread;
use function fwrite;
use function rewind;
use const LIBXML_NOERROR;
use const LIBXML_NONET;
use const LIBXML_NOWARNING;

/**
 * @see ExportCupTableAction
 */
final class ExportCupTableActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_and_returns_the_csv_export(): void
    {
        $this->get('/api/v1/cups/101/export')->assertUnauthorized();
        $this->get('/api/v1/cups/101/export?format=html')->assertUnauthorized();

        $this->seed(SprintCupLineSeeder::class);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        $csv = $this->get('/api/v1/cups/101/export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition')
            ->assertSeeText('Место;ФИО;Год;Клуб;Очки')
            ->assertSeeText('Миссюревич Алексей')
            ->assertSeeText(new CupGroup(GroupMale::Man)->name())
            ->assertSeeText(new CupGroup(GroupMale::Woman)->name())
        ;

        $explicitCsv = $this->get('/api/v1/cups/101/export?format=csv')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv->getContent());
        $this->assertSame($csv->getContent(), $explicitCsv->getContent());

        $html = $this->get('/api/v1/cups/101/export?format=html')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertHeader('Content-Disposition')
            ->assertSee('<!doctype html>', false)
            ->assertSeeText(new CupGroup(GroupMale::Man)->name())
            ->assertSeeText(new CupGroup(GroupMale::Woman)->name())
            ->assertSeeText('Миссюревич Алексей');
        $this->assertStringContainsString('.html', (string) $html->headers->get('Content-Disposition'));
    }

    #[Test]
    public function it_exports_rows_from_the_same_table_as_the_json_view(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        Group::factory(state: ['id' => 105, 'name' => 'Ж21Е'])->createOne();
        Distance::factory(state: ['id' => 105, 'group_id' => 105, 'event_id' => 101, 'length' => 2300, 'points' => 23])->createOne();
        Person::factory(state: ['id' => 105, 'lastname' => 'Иванова', 'firstname' => 'Анна', 'birthday' => '2003-01-01'])->createOne();
        PersonPayment::factory(state: ['id' => 105, 'person_id' => 105, 'date' => '2024-01-01', 'year' => 2024])->createOne();
        ProtocolLine::factory(state: [
            'id' => 105,
            'lastname' => 'Иванова',
            'firstname' => 'Анна',
            'club' => 'Клуб',
            'year' => '2003',
            'rank' => 'I',
            'time' => '00:18:20',
            'place' => '1',
            'distance_id' => 105,
            'person_id' => 105,
        ])->createOne();
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        $export = $this->get('/api/v1/cups/101/export')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition');

        $csv = fopen('php://temp', 'w+b');
        $this->assertNotFalse($csv);
        fwrite($csv, $export->getContent());
        rewind($csv);
        $this->assertSame("\xEF\xBB\xBF", fread($csv, 3));

        $html = $this->get('/api/v1/cups/101/export?format=html')->assertOk();
        $document = new DOMDocument();
        $this->assertTrue($document->loadHTML($html->getContent(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING));
        $sections = $document->getElementsByTagName('section');
        $this->assertCount(2, $sections);

        foreach ([new CupGroup(GroupMale::Man), new CupGroup(GroupMale::Woman)] as $sectionIndex => $group) {
            $table = $this->getJson('/api/v1/cups/101/tables/' . $group->id())->assertOk()->json();
            $this->assertNotEmpty($table);
            $section = $sections->item($sectionIndex);
            $this->assertInstanceOf(DOMElement::class, $section);
            $this->assertSame($group->name(), $section->getElementsByTagName('h2')->item(0)?->textContent);
            $body = $section->getElementsByTagName('tbody')->item(0);
            $this->assertInstanceOf(DOMElement::class, $body);
            $htmlRows = $body->getElementsByTagName('tr');
            $this->assertCount(count($table), $htmlRows);
            $this->assertSame([$group->name()], fgetcsv($csv, 0, ';', '"', ''));
            $this->assertSame(['Место', 'ФИО', 'Год', 'Клуб', 'Очки', '2024-04-12'], fgetcsv($csv, 0, ';', '"', ''));

            foreach ($table as $rowIndex => $row) {
                $expected = [
                    (string) $row['place'],
                    $row['personName'],
                    (string) $row['personYear'],
                    $row['clubName'],
                    $row['totalPoints'],
                    $row['stages']['101']['points'],
                ];
                $this->assertSame($expected, fgetcsv($csv, 0, ';', '"', ''));
                $htmlRow = $htmlRows->item($rowIndex);
                $this->assertInstanceOf(DOMElement::class, $htmlRow);
                $htmlValues = [];
                foreach ($htmlRow->getElementsByTagName('td') as $cell) {
                    $htmlValues[] = $cell->textContent;
                }
                $this->assertSame($expected, $htmlValues);
            }

            $this->assertSame([null], fgetcsv($csv, 0, ';', '"', ''));
        }

        $this->assertFalse(fgetcsv($csv, 0, ';', '"', ''));
        fclose($csv);
    }

    #[Test]
    public function it_rejects_invalid_export_requests_as_json(): void
    {
        $this->getJson('/api/v1/cups/101/export')->assertUnauthorized();
        $this->seed(SprintCupLineSeeder::class);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        $this->getJson('/api/v1/cups/999999/export')->assertNotFound()->assertJsonPath('errors.0.code', 'cup_not_found');
        $this->getJson('/api/v1/cups/999999/export?format=html')->assertNotFound()->assertJsonPath('errors.0.code', 'cup_not_found');
        $this->getJson('/api/v1/cups/101/export?format=pdf')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'format')
            ->assertHeaderMissing('Content-Disposition');
        $this->getJson('/api/v1/cups/101/tables/M_0_/export')->assertNotFound();
    }

    #[Test]
    public function it_exports_headings_for_an_empty_cup(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        CupEvent::query()->where('cup_id', 101)->update(['active' => false]);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

        $this->get('/api/v1/cups/101/export')
            ->assertOk()
            ->assertSeeText('Место;ФИО;Год;Клуб;Очки')
            ->assertDontSeeText('Миссюревич Алексей');

        $this->get('/api/v1/cups/101/export?format=html')
            ->assertOk()
            ->assertSeeText('Место')
            ->assertSeeText(new CupGroup(GroupMale::Man)->name())
            ->assertDontSeeText('Миссюревич Алексей');
    }

    #[Test]
    public function full_export_reuses_cached_tables(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]));

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
}
