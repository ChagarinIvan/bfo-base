<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\PersonPrompt\PersonPromptAssembler;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\Exception\PersonNotFound;
use App\Application\Service\PersonPrompt\Exception\PersonPromptNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPrompt\PersonPromptMetaphone;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class UpdatePersonPromptService
{
    public function __construct(
        private PersonPromptRepository $prompts,
        private PersonPromptAssembler  $assembler,
        private TransactionManager     $transactional,
        private PersonRepository       $persons,
        private PersonPromptMetaphone $metaphone,
        private Clock $clock,
    ) {
    }

    /** @throws PersonPromptNotFound */
    public function execute(UpdatePersonPrompt $command): ViewPersonPromptDto
    {
        return $this->transactional->run(function () use ($command): ViewPersonPromptDto {
            $prompt = $this->prompts->lockById($command->id()) ?? throw new PersonPromptNotFound();
            $this->persons->byId($prompt->person_id) ?? throw new PersonNotFound();
            $input = $command->input();

            $prompt->updateData(
                $input->prompt,
                $this->metaphone->calculate($input->prompt),
                new Impression($this->clock->now(), $input->userId),
            );

            $this->prompts->update($prompt);

            return $this->assembler->toViewPersonPromptDto($prompt);
        });
    }
}
