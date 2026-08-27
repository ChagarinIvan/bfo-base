<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\DeletePersonPrompt;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\DummyTransactional;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DeletePersonPromptServiceTest extends TestCase
{
    private DeletePersonPromptService $service;

    private MockObject&PersonPromptRepository $prompts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DeletePersonPromptService(
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
        $this->prompts->expects($this->never())->method('delete');

        $this->service->execute(new DeletePersonPrompt('5'));
    }

    #[Test]
    public function it_deletes_prompt_and_returns_dto(): void
    {
        /** @var PersonPrompt $prompt */
        $prompt = PersonPrompt::factory()->makeOne(['id' => 5, 'person_id' => 1]);

        $this->prompts->expects($this->once())->method('lockById')->with(5)->willReturn($prompt);
        $this->prompts->expects($this->once())->method('delete')->with($prompt);

        $result = $this->service->execute(new DeletePersonPrompt('5'));

        $this->assertInstanceOf(ViewPersonPromptDto::class, $result);
        $this->assertSame('5', $result->id);
    }
}
