<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Application\Service\PersonPrompt\ChangePersonPrompt;
use App\Application\Service\PersonPrompt\ChangePersonPromptService;
use App\Domain\Auth\Impression;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;

final readonly class SetPersonToProtocolLinesService
{
    public function __construct(
        private ProtocolLineRepository $lines,
        private ChangePersonPromptService $prompts,
        private RebuildPersonRanksService $ranks,
        private Clock $clock,
    ) {
    }

    public function execute(SetPersonToProtocolLines $command): void
    {
        $lines = $this->lines->byCriteria($command->criteria());
        $oldPersonIds = $lines->pluck('person_id')->filter()->unique();

        $lines->each(function (ProtocolLine $line) use ($command): void {
            $line->setPerson($command->personId(), new Impression($this->clock->now(), $command->userId()->id));
            $this->lines->update($line);
        });

        $this->prompts->execute(new ChangePersonPrompt(
            (string) $command->criteria()->param('preparedLine'),
            $command->personId(),
            $command->userId(),
        ));

        $this->ranks->execute(new RebuildPersonRanks($command->personId(), $command->userId()));
        $oldPersonIds->each(fn (int $personId) => $this->ranks->execute(new RebuildPersonRanks($personId, $command->userId())));
    }
}
