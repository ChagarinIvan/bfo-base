<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Person;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ExportPersonRanksAction;
use App\Domain\Club\Club;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function fclose;
use function fgetcsv;
use function fopen;
use function fread;
use function fwrite;
use function rewind;

/** @see ExportPersonRanksAction */
final class ExportPersonRanksActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_and_validates_the_listing_filters(): void
    {
        $this->get('/api/v1/persons/export')->assertUnauthorized();
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret',
        ]));

        $this->getJson('/api/v1/persons/export?rankId=99')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'rankId')
            ->assertHeaderMissing('Content-Disposition');
        $this->getJson('/api/v1/persons/export?name=ab')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'name');
    }

    #[Test]
    public function it_exports_all_matching_active_persons_independent_of_pagination(): void
    {
        Club::factory()->createOne(['id' => 100]);
        Club::factory()->createOne(['id' => 101]);
        for ($index = 1; $index <= 22; ++$index) {
            Person::factory()->createOne([
                'id' => $index,
                'lastname' => 'Іваноў' . $index,
                'firstname' => 'Ян',
                'birthday' => '2001-06-04',
                'club_id' => 100,
            ]);
            DB::table('person')->where('id', $index)->update(['current_rank' => Rank::FirstRank->value]);
        }
        Person::factory()->createOne(['id' => 23, 'lastname' => 'Іваноў', 'birthday' => '2002-01-01', 'club_id' => 100]);
        Person::factory()->createOne(['id' => 24, 'lastname' => 'Іваноў', 'birthday' => '2001-01-01', 'club_id' => 101]);
        Person::factory()->createOne(['id' => 25, 'lastname' => 'Іваноў', 'birthday' => '2001-01-01', 'club_id' => 100, 'active' => false]);

        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret',
        ]));
        $response = $this->get('/api/v1/persons/export?name=%D0%86%D0%B2%D0%B0%D0%BD&clubId=100&rankId=6&birthYear=2001&page=2&perPage=1')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition');

        $stream = fopen('php://temp', 'w+b');
        $this->assertNotFalse($stream);
        fwrite($stream, $response->streamedContent());
        rewind($stream);
        $this->assertSame("\xEF\xBB\xBF", fread($stream, 3));
        $this->assertSame(['lastname', 'firstname', 'birthday', 'rank'], fgetcsv($stream, 0, ';', '"', ''));
        $rows = [];
        while (($row = fgetcsv($stream, 0, ';', '"', '')) !== false) {
            $rows[] = $row;
        }
        fclose($stream);

        $this->assertCount(22, $rows);
        foreach ($rows as $row) {
            $this->assertSame('Ян', $row[1]);
            $this->assertSame('2001', $row[2]);
            $this->assertSame('I', $row[3]);
        }
    }

    #[Test]
    public function it_returns_only_headings_for_an_empty_result_and_a_blank_year_for_missing_birthday(): void
    {
        Person::factory()->createOne(['id' => 1, 'lastname' => 'Альфа', 'birthday' => null]);
        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret',
        ]));

        $this->assertSame(
            "\xEF\xBB\xBFlastname;firstname;birthday;rank\r\n",
            $this->get('/api/v1/persons/export?name=%D0%91%D0%B5%D1%82%D0%B0')->assertOk()->streamedContent(),
        );
        $this->assertStringContainsString(';;б/р', $this->get('/api/v1/persons/export')->assertOk()->streamedContent());
    }
}
