<?php

declare(strict_types=1);

namespace App\Application\Service\Group;

use App\Application\Dto\Group\GroupAssembler;
use App\Application\Dto\Group\ViewGroupDto;
use App\Application\Service\Group\Exception\FailedToUpdateGroup;
use App\Application\Service\Group\Exception\GroupNotFound;
use App\Domain\Group\Exception\GroupAlreadyExists;
use App\Domain\Group\GroupNameNormalizer;
use App\Domain\Group\GroupRepository;
use App\Domain\Group\GroupUpdater;
use App\Domain\Shared\TransactionManager;

final readonly class UpdateGroupInfoService
{
    public function __construct(private GroupRepository $groups, private GroupUpdater $updater, private GroupNameNormalizer $normalizer, private TransactionManager $transactional, private GroupAssembler $assembler)
    {
    }

    public function execute(UpdateGroupInfo $command): ViewGroupDto
    {
        return $this->transactional->run(function () use ($command): ViewGroupDto {
            $group = $this->groups->lockById($command->id()) ?? throw new GroupNotFound();

            $input = $command->input($this->normalizer);

            if ($group->name === $input->info->name && $group->normalize_name === $input->info->normalizeName) {
                return $this->assembler->toViewGroupDto($group);
            }

            try {
                $this->updater->update($group, $input);
            } catch (GroupAlreadyExists $exception) {
                throw new FailedToUpdateGroup($exception->getMessage(), $exception->getCode(), previous: $exception);
            }

            $this->groups->update($group);

            return $this->assembler->toViewGroupDto($group);
        });
    }
}
