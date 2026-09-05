<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\DeletePersonPrompt;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\DummyTransactional;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DeletePersonPromptServiceTest extends TestCase
{
    private DeletePersonPromptService $service;

    private MockObject&PersonPromptRepository $prompts;

    private Clock&MockObject $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DeletePersonPromptService(
            $this->prompts = $this->createMock(PersonPromptRepository::class),
            new PersonPromptAssembler(new AuthAssembler),
            $this->clock = $this->createMock(Clock::class),
            new DummyTransactional,
        );
    }

    #[Test]
    public function it_fails_when_prompt_not_found(): void
    {
        $this->expectException(PersonPromptNotFound::class);

        $this->prompts->expects($this->once())->method('lockById')->with(5)->willReturn(null);
        $this->prompts->expects($this->never())->method('update');

        $this->service->execute(new DeletePersonPrompt('5', new UserId(1)));
    }

    #[Test]
    public function it_deletes_prompt_and_returns_dto(): void
    {
        /** @var PersonPrompt $prompt */
        $prompt = PersonPrompt::factory()->makeOne(['id' => 5, 'person_id' => 1]);

        $this->prompts->expects($this->once())->method('lockById')->with(5)->willReturn($prompt);
        $this->clock->expects($this->once())->method('now')->willReturn(Carbon::parse('2026-01-01'));
        $this->prompts->expects($this->once())->method('update')->with($prompt);

        $result = $this->service->execute(new DeletePersonPrompt('5', new UserId(1)));

        $this->assertInstanceOf(ViewPersonPromptDto::class, $result);
        $this->assertSame('5', $result->id);
    }
}
