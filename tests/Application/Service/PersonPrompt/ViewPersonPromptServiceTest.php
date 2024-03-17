<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Application\Service\PersonPrompt\ViewPersonPrompt;
use App\Application\Service\PersonPrompt\ViewPersonPromptService;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewPersonPromptServiceTest extends TestCase
{
    private PersonPromptRepository&MockObject $personsPrompts;

    private ViewPersonPromptService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->personsPrompts = $this->createMock(PersonPromptRepository::class);
        $this->service = new ViewPersonPromptService($this->personsPrompts, new PersonPromptAssembler(new AuthAssembler));
    }

    /** @test */
    public function it_fails_when_person_prompt_does_not_exist(): void
    {
        $this->expectException(PersonPromptNotFound::class);

        $this->personsPrompts
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $this->service->execute(new ViewPersonPrompt('1'));
    }

    /** @test */
    public function it_views_person_prompt(): void
    {
        /** @var PersonPrompt $personPrompt */
        $personPrompt = PersonPrompt::factory()->makeOne();

        $this->personsPrompts
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($personPrompt)
        ;

        $dto = $this->service->execute(new ViewPersonPrompt('1'));

        $this->assertInstanceOf(ViewPersonPromptDto::class, $dto);
        $this->assertEquals($personPrompt->id, $dto->id);
    }
}
