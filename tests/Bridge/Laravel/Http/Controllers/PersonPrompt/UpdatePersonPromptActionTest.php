<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\PersonPrompt\UpdatePersonPromptAction;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\CreatesApplication;

final class UpdatePersonPromptActionTest extends \Tests\TestCase
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
     * @see UpdatePersonPromptAction::class
     */
    #[Test]
    public function it_updates_person_prompt(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        /** @var Person $person */
        $person = Person::factory()->createOne();
        /** @var PersonPrompt $prompt */
        $prompt = PersonPrompt::factory(state: ['person_id' => $person->id, 'prompt' => 'foo bar'])->createOne();

        $this->post("/persons/prompt/$prompt->id/update", [
            'prompt' => 'test',
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect("/persons/$person->id/prompts")
        ;
    }
}
