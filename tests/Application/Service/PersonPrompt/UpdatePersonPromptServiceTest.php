<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonNotFound;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Application\Service\PersonPrompt\UpdatePersonPrompt;
use App\Application\Service\PersonPrompt\UpdatePersonPromptService;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptMetaphone;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\DummyTransactional;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdatePersonPromptServiceTest extends TestCase
{
    private UpdatePersonPromptService $service;

    private MockObject&PersonPromptRepository $prompts;

    private MockObject&PersonRepository $persons;

    private MockObject&PersonPromptMetaphone $metaphone;

    private Clock&MockObject $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdatePersonPromptService(
            $this->prompts = $this->createMock(PersonPromptRepository::class),
            new PersonPromptAssembler(new AuthAssembler),
            new DummyTransactional,
            $this->persons = $this->createMock(PersonRepository::class),
            $this->metaphone = $this->createMock(PersonPromptMetaphone::class),
            $this->clock = $this->createMock(Clock::class),
        );
    }

    #[Test]
    public function it_fails_when_prompt_not_found(): void
    {
        $this->expectException(PersonPromptNotFound::class);

        $this->prompts->expects($this->once())->method('lockById')->with(5)->willReturn(null);
        $this->prompts->expects($this->never())->method('update');

        $this->service->execute($this->command());
    }

    #[Test]
    public function it_updates_prompt_and_returns_dto(): void
    {
        /** @var PersonPrompt $prompt */
        $prompt = PersonPrompt::factory()->makeOne(['id' => 5, 'person_id' => 1]);

        $this->prompts->expects($this->once())->method('lockById')->with(5)->willReturn($prompt);
        $this->persons->expects($this->once())->method('byId')->with(1)->willReturn($this->createStub(Person::class));
        $this->metaphone->expects($this->once())->method('calculate')->with('Обновлённый промпт')->willReturn('UPD');
        $this->clock->expects($this->once())->method('now')->willReturn(Carbon::now());
        $this->prompts->expects($this->once())->method('update')->with($prompt);

        $result = $this->service->execute($this->command());

        $this->assertInstanceOf(ViewPersonPromptDto::class, $result);
        $this->assertSame('5', $result->id);
    }

    #[Test]
    public function it_fails_when_prompt_person_is_inactive(): void
    {
        /** @var PersonPrompt $prompt */
        $prompt = PersonPrompt::factory()->makeOne(['id' => 5, 'person_id' => 1]);

        $this->prompts->expects($this->once())->method('lockById')->with(5)->willReturn($prompt);
        $this->persons->expects($this->once())->method('byId')->with(1)->willReturn(null);
        $this->metaphone->expects($this->never())->method('calculate');
        $this->prompts->expects($this->never())->method('update');

        $this->expectException(PersonNotFound::class);
        $this->service->execute($this->command());
    }

    private function command(): UpdatePersonPrompt
    {
        $dto = new PersonPromptDto();
        $dto->prompt = 'Обновлённый промпт';

        return new UpdatePersonPrompt($dto, '5', new UserId(1));
    }
}
