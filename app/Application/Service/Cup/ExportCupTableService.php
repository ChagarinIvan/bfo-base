<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupExportAssembler;
use App\Application\Dto\Cup\ExportCupTableDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventResources;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Table\CupTableBuilder;
use App\Domain\Shared\Criteria;

final readonly class ExportCupTableService
{
    public function __construct(
        private CupRepository $cups,
        private CupEventRepository $cupEvents,
        private CupTableBuilder $builder,
        private CupExportAssembler $assembler,
    ) {
    }

    public function execute(ExportCupTable $command): ExportCupTableDto
    {
        $cup = $this->cups->byId($command->cupId()) ?? throw new CupNotFound();
        $events = $this->cupEvents->byCriteria(
            new Criteria(['cupId' => $command->cupId()], ['event.date' => 'asc']),
            new CupEventResources(withCup: true, withEvent: true),
        );

        $sections = [];
        foreach ($cup->groups() as $group) {
            $sections[] = [
                'group' => $group,
                'table' => $this->builder->build($cup, $events, $group),
            ];
        }

        return $this->assembler->toDto($cup, $sections);
    }
}
