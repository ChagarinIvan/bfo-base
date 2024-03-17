<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Competition;

use App\Bridge\Laravel\Http\Controllers\Competition\ShowEditCompetitionFormAction;
use App\Domain\Competition\Competition;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowEditCompetitionFormActionTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    /**
     * @test
     * @see ShowEditCompetitionFormAction::class
     */
    public function it_shows_edit_competition_page(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);
        /** @var Competition $competition */
        $competition = Competition::factory(state: ['name' => 'comp', 'description' => 'old desc'])->createOne();

        $this->get("/competitions/$competition->id/edit")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<input class="form-control" id="name" name="name" value="comp">', false)
            ->assertSee('<input class="form-control" id="description" name="description" value="old desc">', false)
        ;
    }
}
