<?php

declare(strict_types=1);

namespace App\Application\Service\Group;

use App\Application\Service\Group\Exception\GroupNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Group\GroupRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class DeleteGroupService
{
    public function __construct(private GroupRepository $groups, private Clock $clock, private TransactionManager $transactional)
    {
    }

    public function execute(DeleteGroup $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $group = $this->groups->lockById($command->id()) ?? throw new GroupNotFound();
            $group->disable(new Impression($this->clock->now(), $command->userId()));

            $this->groups->update($group);
        });
    }
}
