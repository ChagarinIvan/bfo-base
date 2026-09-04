<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Service\PersonPrompt\ChangePersonPrompt;
use App\Application\Service\PersonPrompt\ChangePersonPromptService;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Domain\Auth\Impression;
use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\DummyTransactional;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ChangePersonPromptServiceTest extends TestCase
{
    private MockObject&PersonPromptRepository $prompts;
    private MockObject&PersonPromptFactory $factory;
    private Clock&MockObject $clock;

    #[Test]
    public function it_creates_a_prompt_with_the_factory_when_it_does_not_exist(): void
    {
        $service = $this->service();
        $created = $this->createStub(PersonPrompt::class);
        $this->prompts->expects($this->once())->method('byCriteria')->willReturn(new Collection());
        $this->factory->expects($this->once())->method('create')->with(self::callback(
            static fn ($input): bool => $input->prompt === 'petrov_ivan'
                && $input->personId === 7
                && $input->userId === 11,
        ))->willReturn($created);
        $this->prompts->expects($this->once())->method('add')->with($created);

        $service->execute(new ChangePersonPrompt('petrov_ivan', 7, new UserId(11)));
    }

    #[Test]
    public function it_reassigns_existing_prompts_through_the_aggregate(): void
    {
        $service = $this->service();
        $prompt = $this->createMock(PersonPrompt::class);
        $prompt->expects($this->atLeast(2))->method('__get')->willReturnMap([
            ['prompt', 'petrov_ivan'],
            ['metaphone', 'PET'],
        ]);
        $this->prompts->expects($this->once())->method('byCriteria')->willReturn(new Collection([$prompt]));
        $this->clock->expects($this->once())->method('now')->willReturn(Carbon::now());
        $prompt->expects($this->once())->method('updateData')->with(
            'petrov_ivan',
            'PET',
            self::isInstanceOf(Impression::class),
            7,
        );
        $this->prompts->expects($this->once())->method('update')->with($prompt);

        $service->execute(new ChangePersonPrompt('petrov_ivan', 7, new UserId(11)));
    }

    private function service(): ChangePersonPromptService
    {
        $this->prompts = $this->createMock(PersonPromptRepository::class);
        $this->factory = $this->createMock(PersonPromptFactory::class);
        $this->clock = $this->createMock(Clock::class);
        $deleter = new DeletePersonPromptService(
            $this->prompts,
            new PersonPromptAssembler(new AuthAssembler),
            $this->clock,
            new DummyTransactional,
        );

        return new ChangePersonPromptService($this->prompts, $this->factory, $deleter, $this->clock);
    }
}
