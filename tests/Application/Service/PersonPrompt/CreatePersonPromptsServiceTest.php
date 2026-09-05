<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\PersonPrompt\CreatePersonPrompts;
use App\Application\Service\PersonPrompt\CreatePersonPromptsService;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptGenerator;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Clock;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class CreatePersonPromptsServiceTest extends TestCase
{
    private MockObject&PersonRepository $persons;
    private MockObject&PersonPromptRepository $prompts;
    private MockObject&PersonPromptGenerator $generator;
    private MockObject&PersonPromptFactory $factory;
    private Clock&MockObject $clock;

    #[Test]
    public function it_creates_only_missing_generated_prompts_without_relations(): void
    {
        $this->setUpCollaborators();
        $this->persons->expects($this->once())->method('byCriteria')->willReturn(new Collection());
        $this->generator->expects($this->once())->method('generate')->with('Ivan', 'Petrov', '1990', false)
            ->willReturn(['petrov_ivan', 'ivan_petrov']);
        $existing = new class {
            public string $prompt = 'petrov_ivan';
        };
        $this->prompts->expects($this->once())->method('byCriteria')->willReturn(new Collection([$existing]));
        $created = $this->createStub(PersonPrompt::class);
        $this->factory->expects($this->once())->method('create')->with(self::callback(
            static fn ($input): bool => $input->prompt === 'ivan_petrov'
                && $input->personId === 7
                && $input->userId === 11,
        ))->willReturn($created);
        $this->prompts->expects($this->once())->method('add')->with($created);

        $this->service()->execute(new CreatePersonPrompts(7, 'Ivan', 'Petrov', '1990', new UserId(11)));
    }

    private function setUpCollaborators(): void
    {
        $this->persons = $this->createMock(PersonRepository::class);
        $this->prompts = $this->createMock(PersonPromptRepository::class);
        $this->generator = $this->createMock(PersonPromptGenerator::class);
        $this->factory = $this->createMock(PersonPromptFactory::class);
        $this->clock = $this->createMock(Clock::class);
    }

    private function service(): CreatePersonPromptsService
    {
        return new CreatePersonPromptsService($this->persons, $this->prompts, $this->generator, $this->factory, $this->clock);
    }
}
