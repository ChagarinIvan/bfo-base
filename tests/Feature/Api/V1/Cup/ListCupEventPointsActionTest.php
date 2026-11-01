<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ListCupEventPointsAction;
use App\Domain\Auth\Impression;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastructure\Sanctum\SanctumUser;
use Carbon\Carbon;
use Database\Seeders\SprintCupLineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see ListCupEventPointsAction */
final class ListCupEventPointsActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_exposes_protocol_line_impressions_in_points_only_with_bearer_authentication(): void
    {
        $this->seed(SprintCupLineSeeder::class);
        $user = SanctumUser::query()->create(['email' => 'audit@example.test', 'password' => 'secret']);
        $created = new Impression(Carbon::parse('2026-10-27T10:00:00+00:00'), $user->id);
        $updated = new Impression(Carbon::parse('2026-11-01T12:30:00+00:00'), $user->id);
        $line = ProtocolLine::query()->findOrFail(101);
        $line->created = $created;
        $line->updated = $updated;
        $line->save();
        $url = '/api/v1/cup-events/101/points?groupId=M_0_&perPage=2';

        $this->getJson($url)->assertOk()->assertJsonMissingPath('0.created')->assertJsonMissingPath('0.updated');
        $this->withToken($user->createToken('audit')->plainTextToken)
            ->getJson($url)->assertOk()
            ->assertJsonPath('0.personName', 'Миссюревич Алексей')
            ->assertJsonPath('0.created', ['at' => $created->at->toAtomString(), 'by' => (string) $created->by])
            ->assertJsonPath('0.updated', ['at' => $updated->at->toAtomString(), 'by' => (string) $updated->by]);
        $this->assertEquals($created, $line->fresh()->created);
        $this->assertEquals($updated, $line->fresh()->updated);
    }

    #[Test]
    public function it_validates_the_required_group_and_name_filter(): void
    {
        $this->getJson('/api/v1/cup-events/1/points')
            ->assertUnprocessable()
            ->assertJsonFragment(['code' => 'validation_error', 'field' => 'groupId'])
        ;

        $this->getJson('/api/v1/cup-events/1/points?groupId=M21&name=ab')
            ->assertUnprocessable()
            ->assertJsonFragment(['code' => 'validation_error', 'field' => 'name'])
        ;
    }

    #[Test]
    public function it_returns_not_found_for_a_missing_cup_event(): void
    {
        $this->getJson('/api/v1/cup-events/999/points?groupId=M21')
            ->assertNotFound()
            ->assertJsonFragment(['code' => 'cup_event_not_found'])
        ;
    }

    #[Test]
    public function it_returns_paginated_calculated_points_for_the_selected_cup_group(): void
    {
        $this->seed(SprintCupLineSeeder::class);

        $this->getJson('/api/v1/cup-events/101/points?groupId=M_0_&perPage=2')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.personName', 'Миссюревич Алексей')
            ->assertJsonPath('0.points', '1000')
            ->assertHeader('X-Pagination-Per-Page', '2')
            ->assertHeader('X-Pagination-Has-Next', 'true')
        ;
    }
}
