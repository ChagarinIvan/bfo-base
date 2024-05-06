<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Domain\Cup\CupRepository;
use App\Domain\CupEvent\CupEvent;
use App\Domain\CupEvent\CupEventRepository;
use function array_map;

final readonly class ListCupEventService
{
    public function __construct(
        private CupRepository $cups,
        private CupEventRepository $cupEvents,
        private CupEventAssembler $assembler,
    ) {
    }

    /** @return ViewCupEventDto[] */
    public function execute(ListCupEvent $command): array
    {
        return array_map(
            fn (CupEvent $ce) => $this->assembler->toViewCupEventDto($ce, $this->cups),
            $this->cupEvents->byCriteria($command->criteria())->all()
        );
    }
}
