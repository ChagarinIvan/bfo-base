<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Domain\Auth\Impression;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class DeletePersonPromptService
{
    public function __construct(
        private PersonPromptRepository $prompts,
        private PersonPromptAssembler $assembler,
        private Clock $clock,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws PersonPromptNotFound */
    public function execute(DeletePersonPrompt $command): ViewPersonPromptDto
    {
        return $this->transactional->run(function () use ($command): ViewPersonPromptDto {
            $prompt = $this->prompts->lockById($command->id()) ?? throw new PersonPromptNotFound();
            $prompt->disable(new Impression($this->clock->now(), $command->userId()));
            $this->prompts->update($prompt);

            return $this->assembler->toViewPersonPromptDto($prompt);
        });
    }
}
