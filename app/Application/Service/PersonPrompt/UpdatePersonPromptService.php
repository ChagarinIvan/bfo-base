<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\PersonPrompt\PersonPromptUpdater;
use App\Domain\Shared\TransactionManager;

final readonly class UpdatePersonPromptService
{
    public function __construct(
        private PersonPromptUpdater $updater,
        private PersonPromptRepository $prompts,
        private PersonPromptAssembler $assembler,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws PersonPromptNotFound */
    public function execute(UpdatePersonPrompt $command): ViewPersonPromptDto
    {
        return $this->transactional->run(function () use ($command): ViewPersonPromptDto {
            $prompt = $this->prompts->lockById($command->id()) ?? throw new PersonPromptNotFound();
            $this->updater->update($prompt, $command->input());
            $this->prompts->update($prompt);

            return $this->assembler->toViewPersonPromptDto($prompt);
        });
    }
}
