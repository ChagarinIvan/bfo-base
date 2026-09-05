<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\SearchPersonPromptDto;
use App\Application\Service\PersonPrompt\ListPersonsPrompts;
use App\Application\Service\PersonPrompt\ListPersonsPromptsService;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Pagerfanta\Adapter\ArrayAdapter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListPersonsPromptsServiceTest extends TestCase
{
    #[Test]
    public function it_paginates_person_prompts_by_criteria(): void
    {
        $repository = $this->createMock(PersonPromptRepository::class);
        $repository->expects($this->once())
            ->method('paginate')
            ->with(new Criteria(['personId' => '7', 'activePerson' => true]))
            ->willReturn(new Slice(new ArrayAdapter([])));

        $service = new ListPersonsPromptsService(
            $repository,
            new PersonPromptAssembler(new AuthAssembler),
        );

        $result = $service->paginate(new ListPersonsPrompts(new SearchPersonPromptDto(personId: '7')));

        $this->assertInstanceOf(Slice::class, $result);
    }
}
