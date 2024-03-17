<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\SearchPersonPromptDto;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\ListPersonsPrompts;
use App\Application\Service\PersonPrompt\ListPersonsPromptsService;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListPersonsPromptsServiceTest extends TestCase
{
    private PersonPromptRepository&MockObject $personsPrompts;

    private ListPersonsPromptsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->personsPrompts = $this->createMock(PersonPromptRepository::class);
        $this->service = new ListPersonsPromptsService($this->personsPrompts, new PersonPromptAssembler(new AuthAssembler));
    }

    /** @test */
    public function it_gets_list_of_person_prompts(): void
    {
        $this->personsPrompts
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1])))
            ->willReturn(PersonPrompt::factory(2)->make())
        ;

        $list = $this->service->execute(new ListPersonsPrompts(new SearchPersonPromptDto(personId: '1')));

        $this->assertCount(2, $list);
        $this->assertContainsOnlyInstancesOf(ViewPersonPromptDto::class, $list);
    }
}
