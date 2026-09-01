<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Club\ClubDto;
use App\Domain\Club\ClubInfo;
use App\Domain\Club\ClubInput;
use App\Domain\Club\Factory\ClubNameNormalizer;
use function trim;

final readonly class AddClub
{
    public function __construct(
        private ClubDto $dto,
        private UserId $userId,
    ) {
    }

    public function clubInput(ClubNameNormalizer $normalizer): ClubInput
    {
        return new ClubInput(
            info: $this->clubInfo($normalizer),
            userId: $this->userId->id,
        );
    }

    private function clubInfo(ClubNameNormalizer $normalizer): ClubInfo
    {
        $name = trim($this->dto->name);

        return new ClubInfo(
            name: $name,
            normalizeName: $normalizer->normalize($name),
        );
    }
}
