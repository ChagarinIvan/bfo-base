<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupTableAssembler;
use App\Application\Dto\Cup\ViewCupTableDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventResources;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Table\CupTableBuilder;
use App\Domain\Shared\Criteria;

final readonly class ViewCupTableService
{
    public function __construct(
        private CupRepository $cups,
        private CupEventRepository $cupEvents,
        private CupTableBuilder $table,
        private CupTableAssembler $assembler,
    ) {
    }

    /** @throws CupNotFound */
    public function execute(ViewCupTable $command): ViewCupTableDto
    {
        $cup = $this->cups->byId((int) $command->cupId) ?? throw new CupNotFound();
        $events = $this->cupEvents
            ->byCriteria(
                new Criteria(['cupId' => (int) $command->cupId], ['event.date' => 'asc']),
                new CupEventResources(withCup: true, withEvent: true),
            )
        ;

        return $this->assembler->toViewCupTableDto(
            $this->table->build(
                cup: $cup,
                events: $events,
                group: $command->group,
            ),
        );
    }
}
