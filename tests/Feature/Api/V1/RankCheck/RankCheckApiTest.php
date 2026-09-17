<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\RankCheck;

use App\Domain\Auth\Impression;
use App\Domain\RankCheck\Event\RankCheckCreated;
use App\Domain\RankCheck\RankCheck;
use App\Domain\RankCheck\RankCheckRow;
use App\Domain\RankCheck\RankCheckStatus;
use App\Infrastructure\Sanctum\SanctumUser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RankCheckApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_to_create_a_check(): void
    {
        $this->post('/api/v1/rank-checks', [
            'list' => $this->file(),
        ])->assertUnauthorized();
    }

    #[Test]
    public function an_authenticated_user_can_create_a_pending_check(): void
    {
        Event::fake([RankCheckCreated::class]);
        $user = $this->createUser();
        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/rank-checks', [
            'list' => $this->file(),
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('status', RankCheckStatus::Parsing->value)
            ->assertJsonPath('created.by', (string) $user->id)
            ->assertJsonStructure(['id', 'status', 'created', 'updated'])
        ;
        $this->assertDatabaseHas('rank_checks', ['status' => RankCheckStatus::Parsing->value]);
    }

    #[Test]
    public function it_rejects_a_non_csv_rank_check_file(): void
    {
        Sanctum::actingAs($this->createUser());

        $this->post('/api/v1/rank-checks', [
            'list' => UploadedFile::fake()->createWithContent('list.txt', 'invalid'),
        ])
            ->assertStatus(400)
            ->assertJsonPath('errors.0.code', 'invalid_rank_check_list')
        ;
    }

    #[Test]
    public function an_authenticated_user_can_view_a_check_and_its_paginated_rows(): void
    {
        Sanctum::actingAs($this->createUser());
        $check = $this->createCheck(RankCheckStatus::Ready);
        RankCheckRow::query()->create([
            'rank_check_id' => $check->id,
            'position' => 1,
            'name' => 'Иванов Иван',
            'rank' => 'I',
            'has_person' => true,
            'is_equal' => true,
        ]);

        $this->getJson("/api/v1/rank-checks/{$check->id}")
            ->assertOk()
            ->assertJsonPath('id', (string) $check->id)
            ->assertJsonPath('status', RankCheckStatus::Ready->value)
            ->assertJsonStructure(['created', 'updated'])
            ->assertJsonMissingPath('rows')
        ;

        $this->getJson("/api/v1/rank-checks/{$check->id}/rows?page=1&perPage=1")
            ->assertOk()
            ->assertJsonPath('0.position', 1)
            ->assertJsonPath('0.isEqual', true)
            ->assertHeader('X-Pagination-Has-Next', 'false')
            ->assertHeaderMissing('X-Pagination-Total')
            ->assertHeaderMissing('X-Pagination-Last-Page')
        ;
    }

    #[Test]
    public function an_authenticated_user_can_list_checks_with_pagination(): void
    {
        Sanctum::actingAs($this->createUser());
        $this->createCheck(RankCheckStatus::Ready);
        $this->createCheck(RankCheckStatus::Parsing);

        $this->getJson('/api/v1/rank-checks?page=1&perPage=1')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonStructure([['id', 'status', 'created', 'updated']])
            ->assertHeader('X-Pagination-Has-Next', 'true')
            ->assertHeaderMissing('X-Pagination-Total')
            ->assertHeaderMissing('X-Pagination-Last-Page')
        ;
    }

    private function file(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'list.csv',
            "group;name;club;rank;number;year\nМ21;Иванов Иван;СК Олимп;I;1;1990",
        );
    }

    private function createCheck(RankCheckStatus $status): RankCheck
    {
        $impression = new Impression(Carbon::now(), 10);
        $check = new RankCheck();
        $check->status = $status;
        $check->source_path = 'rank-checks/test.csv';
        $check->created = $impression;
        $check->updated = $impression;
        $check->save();

        return RankCheck::query()->findOrFail($check->id);
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
