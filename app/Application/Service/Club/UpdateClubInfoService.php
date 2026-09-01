<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\Exception\ClubNotFound;
use App\Application\Service\Club\Exception\FailedToUpdateClub;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\ClubUpdater;
use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Club\Factory\ClubNameNormalizer;
use App\Domain\Shared\TransactionManager;

final readonly class UpdateClubInfoService
{
    public function __construct(
        private ClubRepository $clubs,
        private ClubUpdater $updater,
        private ClubNameNormalizer $normalizer,
        private ClubAssembler $assembler,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws ClubNotFound|FailedToUpdateClub */
    public function execute(UpdateClubInfo $command): ViewClubDto
    {
        return $this->transactional->run(function () use ($command): ViewClubDto {
            $club = $this->clubs->lockById($command->id()) ?? throw new ClubNotFound();
            $input = $command->input($this->normalizer);

            if ($club->name === $input->info->name && $club->normalize_name === $input->info->normalizeName) {
                return $this->assembler->toViewClubDto($club);
            }

            try {
                $this->updater->update($club, $command->input($this->normalizer));
            } catch (ClubAlreadyExist $exception) {
                throw FailedToUpdateClub::dueError($exception);
            }

            $this->clubs->update($club);

            return $this->assembler->toViewClubDto($club);
        });
    }
}
