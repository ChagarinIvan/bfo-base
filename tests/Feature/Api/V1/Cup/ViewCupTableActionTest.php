<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ViewCupTableAction;
use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\UpdatePersonAction;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Event\Event;
use App\Domain\Person\Citizenship;
use App\Domain\Person\Person;
use App\Infrastructure\Sanctum\SanctumUser;
use Database\Seeders\SprintCupLineSeeder;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see ViewCupTableAction
 * @see UpdatePersonAction
 */
final class ViewCupTableActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_a_cup_table(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        Event::factory(state: [
            'id' => 102,
            'competition_id' => 101,
            'name' => 'First by date',
            'date' => '2024-04-10',
        ])->createOne();
        CupEvent::factory(state: ['id' => 102, 'cup_id' => 101, 'event_id' => 102, 'points' => 1000])->createOne();

        $this->getJson('/api/v1/cups/101/tables/M_0_')
            ->assertOk()
            ->assertJsonStructure([['place', 'personId', 'personName', 'personYear', 'clubName', 'stages', 'totalPoints', 'averagePoints']])
            ->assertHeader('X-Pagination-Current-Page', '1')
            ->assertHeader('X-Pagination-Per-Page', '20')
        ;
    }

    #[Test]
    public function it_displays_and_searches_the_athlete_profile(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        Person::query()->whereKey(101)->update([
            'lastname' => 'Profile',
            'firstname' => 'Athlete',
            'birthday' => '2003-05-01',
        ]);

        $this->getJson('/api/v1/cups/101/tables/M_0_?name=Profile')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.personId', '101')
            ->assertJsonPath('0.personName', 'Profile Athlete')
            ->assertJsonPath('0.personYear', 2003)
        ;
    }

    #[Test]
    public function it_refreshes_a_cached_table_after_the_athlete_profile_is_edited(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        $url = '/api/v1/cups/101/tables/M_0_';
        $this->getJson($url)->assertOk()->assertJsonFragment(['personId' => '101', 'personYear' => 2004]);

        Sanctum::actingAs(SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => $this->app->make(Hasher::class)->make('secret'),
        ]));
        $this->putJson('/api/v1/persons/101', [
            'lastname' => 'Updated',
            'firstname' => 'Athlete',
            'birthday' => null,
            'clubId' => null,
            'citizenship' => Citizenship::BELARUS->value,
        ])->assertOk();

        $this->getJson($url)
            ->assertOk()
            ->assertJsonFragment(['personId' => '101', 'personName' => 'Updated Athlete', 'personYear' => 0])
        ;
    }

    #[Test]
    public function it_omits_inactive_people_from_the_public_table(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        Person::query()->whereKey(101)->update(['active' => false]);

        $this->getJson('/api/v1/cups/101/tables/M_0_')
            ->assertOk()
            ->assertJsonMissing(['personId' => '101'])
        ;
    }

    #[Test]
    public function it_rejects_a_name_filter_shorter_than_three_characters(): void
    {
        $this->getJson('/api/v1/cups/101/tables/M_0_?name=Jo')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.field', 'name')
        ;
    }

    #[Test]
    public function it_rejects_a_malformed_group_id_before_the_factory_is_called(): void
    {
        $this->getJson('/api/v1/cups/101/tables/M_13_')
            ->assertUnprocessable()
        ;
    }

    #[Test]
    public function it_returns_bad_request_when_the_group_is_not_supported_by_the_cup(): void
    {
        $this->seed(SprintCupLineSeeder::class);

        $this->getJson('/api/v1/cups/101/tables/M_12_')
            ->assertBadRequest()
            ->assertJsonPath('errors.0.code', 'cup_group_not_supported')
            ->assertJsonPath('errors.0.message', 'Cup 101 does not support group M_12_.')
        ;
    }
}
