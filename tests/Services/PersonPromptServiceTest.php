<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Services\PersonPromptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Tests\CreatesApplication;
use Tests\TestCase;

final class PersonPromptServiceTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    /**
     * @test
     */
    public function ident_persons_by_prompts(): void
    {
        Person::factory(state: ['id' => 1, 'active' => false])->createOne();
        Person::factory(state: ['id' => 2, 'active' => true])->createOne();

        PersonPrompt::factory(state: ['person_id' => 1, 'prompt' => 'test1'])->createOne();
        PersonPrompt::factory(state: ['person_id' => 2, 'prompt' => 'test2'])->createOne();

        /** @var PersonPromptService $service */
        $service = $this->app->get(PersonPromptService::class);
        $linePersons = $service->identPersonsByPrompts(['test1', 'test2', 'test3']);

        $this->assertEquals(['test2' => 2], $linePersons);
    }
}
