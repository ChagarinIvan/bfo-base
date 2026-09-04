<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\AddPersonPrompt;
use App\Application\Service\PersonPrompt\AddPersonPromptService;
use App\Application\Service\PersonPrompt\Exception\PersonNotFound;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AddPersonPromptServiceTest extends TestCase
{
    private AddPersonPromptService $service;

    private MockObject&PersonPromptFactory $factory;

    private MockObject&PersonPromptRepository $prompts;

    private MockObject&PersonRepository $persons;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AddPersonPromptService(
            $this->factory = $this->createMock(PersonPromptFactory::class),
            $this->prompts = $this->createMock(PersonPromptRepository::class),
            new PersonPromptAssembler(new AuthAssembler),
            $this->persons = $this->createMock(PersonRepository::class),
        );
    }

    #[Test]
    public function it_creates_prompt_via_factory_and_persists_it(): void
    {
        /** @var PersonPrompt $prompt */
        $prompt = PersonPrompt::factory()->makeOne(['id' => 7, 'person_id' => 1]);

        $this->factory->expects($this->once())->method('create')->willReturn($prompt);
        $this->persons->expects($this->once())->method('byId')->with(1)->willReturn($this->createStub(Person::class));
        $this->prompts->expects($this->once())->method('add')->with($prompt);

        $dto = new PersonPromptDto();
        $dto->prompt = 'Иван Иванов';

        $result = $this->service->execute(new AddPersonPrompt($dto, '1', new UserId(1)));

        $this->assertInstanceOf(ViewPersonPromptDto::class, $result);
        $this->assertSame('7', $result->id);
        $this->assertSame('1', $result->personId);
    }

    #[Test]
    public function it_rejects_an_unknown_person(): void
    {
        $this->persons->expects($this->once())->method('byId')->with(1)->willReturn(null);
        $this->factory->expects($this->never())->method('create');

        $dto = new PersonPromptDto();
        $dto->prompt = 'Иван Иванов';

        $this->expectException(PersonNotFound::class);
        $this->service->execute(new AddPersonPrompt($dto, '1', new UserId(1)));
    }
}
