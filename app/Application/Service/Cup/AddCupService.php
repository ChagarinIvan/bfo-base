<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\ViewCupDto;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Factory\CupFactory;

final readonly class AddCupService
{
    public function __construct(
        private CupFactory $factory,
        private CupRepository $cups,
        private CupAssembler $assembler,
    ) {
    }

    public function execute(AddCup $command): ViewCupDto
    {
        $cup = $this->factory->create($command->cupInput());
        $this->cups->add($cup);

        return $this->assembler->toViewCupDto($cup);
    }
}
