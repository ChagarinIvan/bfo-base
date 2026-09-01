<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Club\ClubDto;
use App\Domain\Club\ClubInfo;
use App\Domain\Club\ClubInput;
use App\Domain\Club\ClubNameNormalizer;
use function trim;

final readonly class UpdateClubInfo
{
    public function __construct(
        private string $id,
        private ClubDto $dto,
        private UserId $userId,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function input(ClubNameNormalizer $normalizer): ClubInput
    {
        $name = trim($this->dto->name);

        return new ClubInput(
            info: new ClubInfo(
                name: $name,
                normalizeName: $normalizer->normalize($name),
            ),
            userId: $this->userId->id,
        );
    }

    public function userId(): int
    {
        return $this->userId->id;
    }
}
