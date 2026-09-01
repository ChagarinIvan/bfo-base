<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Competition;

use App\Domain\Competition\Competition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ViewCompetitionActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_an_active_competition_to_a_public_client(): void
    {
        $competition = $this->createCompetition([
            'name' => 'Minsk Cup',
            'from' => '2026-05-10',
            'to' => '2026-05-12',
            'active' => true,
        ]);

        $this->getJson("/api/v1/competitions/{$competition->id}")
            ->assertOk()
            ->assertJsonPath('id', (string) $competition->id)
            ->assertJsonPath('name', 'Minsk Cup')
            ->assertJsonMissingPath('created')
            ->assertJsonMissingPath('updated')
        ;
    }

    #[Test]
    public function it_returns_not_found_for_missing_or_inactive_competitions(): void
    {
        $inactiveCompetition = $this->createCompetition([
            'active' => false,
        ]);

        $this->getJson('/api/v1/competitions/999999')
            ->assertNotFound()
            ->assertJsonPath('errors.0.code', 'competition_not_found')
        ;
        $this->getJson("/api/v1/competitions/{$inactiveCompetition->id}")
            ->assertNotFound()
            ->assertJsonPath('errors.0.code', 'competition_not_found')
        ;
    }

    private function createCompetition(array $attributes = []): Competition
    {
        $model = Competition::factory()->createOne($attributes);

        return Competition::query()->findOrFail($model->getKey());
    }
}
