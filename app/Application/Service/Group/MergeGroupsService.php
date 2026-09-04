<?php

declare(strict_types=1);

namespace App\Application\Service\Group;

use App\Application\Service\Group\Exception\CannotMergeSameGroup;
use App\Application\Service\Group\Exception\GroupNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Distance\DistanceMover;
use App\Domain\Group\GroupRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class MergeGroupsService
{
    public function __construct(
        private GroupRepository $groups,
        private DistanceMover $distances,
        private Clock $clock,
        private TransactionManager $transactional,
    ) {
    }

    public function execute(MergeGroups $command): void
    {
        if ($command->sourceId() === $command->targetId()) {
            throw new CannotMergeSameGroup();
        }

        $this->transactional->run(function () use ($command): void {
            $source = $this->groups->lockById($command->sourceId()) ?? throw new GroupNotFound();
            $this->groups->lockById($command->targetId()) ?? throw new GroupNotFound();
            $this->distances->moveFromGroupToGroup($source->id, $command->targetId());
            $source->disable(new Impression($this->clock->now(), $command->userId()));

            $this->groups->update($source);
        });
    }
}
