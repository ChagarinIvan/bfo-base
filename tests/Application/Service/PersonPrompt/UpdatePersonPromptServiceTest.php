<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Application\Service\PersonPrompt\UpdatePersonPrompt;
use App\Application\Service\PersonPrompt\UpdatePersonPromptService;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\PersonPrompt\PersonPromptUpdater;
use App\Domain\Shared\DummyTransactional;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdatePersonPromptServiceTest extends TestCase
{
    private UpdatePersonPromptService $service;

    private MockObject&PersonPromptUpdater $updater;

    private MockObject&PersonPromptRepository $prompts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdatePersonPromptService(
            $this->updater = $this->createMock(PersonPromptUpdater::class),
            $this->prompts = $this->createMock(PersonPromptRepository::class),
            new PersonPromptAssembler(new AuthAssembler),
            new DummyTransactional,
        );
    }

    #[Test]
    public function it_fails_when_prompt_not_found(): void
    {
        $this->expectException(PersonPromptNotFound::class);

        $this->prompts->expects($this->once())->method('lockById')->with(5)->willReturn(null);
        $this->updater->expects($this->never())->method('update');
        $this->prompts->expects($this->never())->method('update');

        $this->service->execute($this->command());
    }

    #[Test]
    public function it_updates_prompt_and_returns_dto(): void
    {
        /** @var PersonPrompt $prompt */
        $prompt = PersonPrompt::factory()->makeOne(['id' => 5, 'person_id' => 1]);

        $this->prompts->expects($this->once())->method('lockById')->with(5)->willReturn($prompt);
        $this->updater->expects($this->once())->method('update')->with($prompt);
        $this->prompts->expects($this->once())->method('update')->with($prompt);

        $result = $this->service->execute($this->command());

        $this->assertInstanceOf(ViewPersonPromptDto::class, $result);
        $this->assertSame('5', $result->id);
    }

    private function command(): UpdatePersonPrompt
    {
        $dto = new PersonPromptDto();
        $dto->prompt = 'Обновлённый промпт';

        return new UpdatePersonPrompt($dto, '5', new UserId(1));
    }
}
