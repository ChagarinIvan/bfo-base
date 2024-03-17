<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\PersonPrompt\ShowPersonPromptsListAction;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowPersonPromptsListActionTest extends TestCase
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
     * @see ShowPersonPromptsListAction::class
     */
    public function it_shows_person_prompts_list(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Person $person */
        $person = Person::factory()->createOne();
        PersonPrompt::factory(state: ['person_id' => $person->id, 'prompt' => 'test1'])->createOne();
        PersonPrompt::factory(state: ['person_id' => $person->id, 'prompt' => 'test2'])->createOne();

        $this->get("/persons/$person->id/prompts")
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeText([
                'test1',
                'test2',
            ])
        ;
    }
}
