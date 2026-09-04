<?php

declare(strict_types=1);

namespace App\Application\Service\Group;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Group\GroupDto;
use App\Domain\Group\GroupInfo;
use App\Domain\Group\GroupInput;
use App\Domain\Group\GroupNameNormalizer;
use function trim;

final readonly class UpdateGroupInfo
{
    public function __construct(
        private string $id,
        private GroupDto $info,
        private UserId $userId,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function userId(): int
    {
        return $this->userId->id;
    }

    public function input(GroupNameNormalizer $normalizer): GroupInput
    {
        $name = trim($this->info->name);

        return new GroupInput(new GroupInfo($name, $normalizer->normalize($name)), $this->userId->id);
    }
}
