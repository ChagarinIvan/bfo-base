<?php

declare(strict_types=1);

namespace Bridge\Laravel\Http\Controllers\Competition;

use App\Bridge\Laravel\Http\Controllers\Competition\UpdateCompetitionAction;
use App\Models\Competition;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class UpdateCompetitionActionTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $app = $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    /**
     * @test
     * @see UpdateCompetitionAction::class
     */
    public function it_updates_competition_info(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();

        $this->post("/competitions/$competition->id/update", [
                'name' => 'new name',
                'description' => 'new description',
                'from' => '2023-02-01',
                'to' => '2023-02-02',
            ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect("/competitions/$competition->id/show");
        ;

        $this->assertDatabaseHas('competitions', [
            'name' => 'new name',
            'description' => 'new description',
            'from' => '2023-02-01',
            'to' => '2023-02-02',
            'updated_by' => $user->id,
        ]);
    }
}
