<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\PersonPrompt\ShowEditPromptAction;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowEditPromptActionTest extends TestCase
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
     * @see ShowEditPromptAction::class
     */
    public function it_shows_edit_person_prompt_page(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Person $person */
        $person = Person::factory()->createOne();
        /** @var PersonPrompt $prompt */
        $prompt = PersonPrompt::factory(state: ['person_id' => $person->id])->createOne();

        $this->get("/persons/prompt/$prompt->id/edit")
            ->assertStatus(Response::HTTP_OK)
            ->assertSee("<input class=\"form-control \" id=\"prompt\" name=\"prompt\" value=\"$prompt->prompt\" />", false)
        ;
    }
}
