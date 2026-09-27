<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ListCupEventPointsAction;
use Database\Seeders\SprintCupLineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see ListCupEventPointsAction */
final class ListCupEventPointsActionTest extends TestCase
{
    use RefreshDatabase;

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
