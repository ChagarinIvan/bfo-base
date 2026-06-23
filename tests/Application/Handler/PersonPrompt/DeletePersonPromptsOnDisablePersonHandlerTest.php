<?php

declare(strict_types=1);

namespace Tests\Application\Handler\PersonPrompt;

use App\Application\Handler\PersonPrompt\DeletePersonPromptsOnDisablePersonHandler;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Application\Service\PersonPrompt\ListPersonsPromptsService;
use App\Domain\Person\Event\PersonDisabled;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DeletePersonPromptsOnDisablePersonHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    #[Test]
    public function it_deletes_prompts_for_disabled_person(): void
    {
        /** @var Person $disabledPerson */
        $disabledPerson = Person::factory(state: ['id' => 1, 'active' => false])->createOne();
        Person::factory(state: ['id' => 2, 'active' => true])->createOne();

        PersonPrompt::factory(state: ['id' => 101, 'person_id' => 1])->createOne();
        PersonPrompt::factory(state: ['id' => 102, 'person_id' => 1])->createOne();
        PersonPrompt::factory(state: ['id' => 201, 'person_id' => 2])->createOne();

        $handler = new DeletePersonPromptsOnDisablePersonHandler(
            $this->app->get(ListPersonsPromptsService::class),
            $this->app->get(DeletePersonPromptService::class),
        );

        $handler->handle(new PersonDisabled($disabledPerson));

        $this->assertDatabaseMissing('persons_prompt', ['id' => 101]);
        $this->assertDatabaseMissing('persons_prompt', ['id' => 102]);
        $this->assertDatabaseHas('persons_prompt', ['id' => 201, 'person_id' => 2]);
    }
}
