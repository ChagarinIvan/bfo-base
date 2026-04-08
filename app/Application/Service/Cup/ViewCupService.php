<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Cup\CupRepository;

final readonly class ViewCupService
{
    public function __construct(
        private CupRepository $cups,
        private CupAssembler $assembler,
    ) {
    }

    /** @throws CupNotFound */
    public function execute(ViewCup $command): ViewCupDto
    {
        $cup = $this->cups->byId($command->id()) ?? throw new CupNotFound;

        return $this->assembler->toViewCupDto($cup);
    }
}
