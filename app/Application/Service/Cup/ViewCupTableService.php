<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupTableAssembler;
use App\Application\Dto\Cup\ViewCupTableDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\Exception\UnsupportedCupGroup;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventResources;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Exception\CupGroupNotSupported as DomainCupGroupNotSupported;
use App\Domain\Cup\Table\CupTableBuilder;
use App\Domain\Cup\Table\CupTableRow;
use App\Domain\Shared\Criteria;
use function array_filter;
use function array_values;
use function mb_strtolower;
use function str_contains;

final readonly class ViewCupTableService
{
    public function __construct(
        private CupRepository $cups,
        private CupEventRepository $cupEvents,
        private CupTableBuilder $table,
        private CupTableAssembler $assembler,
    ) {
    }

    /** @throws CupNotFound|UnsupportedCupGroup */
    public function execute(ViewCupTable $command): ViewCupTableDto
    {
        $cup = $this->cups->byId((int) $command->cupId) ?? throw new CupNotFound();
        $group = $command->group;

        try {
            $cup->assertGroupSupported($group);
        } catch (DomainCupGroupNotSupported $exception) {
            throw new UnsupportedCupGroup($exception);
        }

        $events = $this->cupEvents
            ->byCriteria(
                new Criteria(['cupId' => (int) $command->cupId], ['event.date' => 'asc']),
                new CupEventResources(withCup: true, withEvent: true),
            )
        ;

        $table = $this->table->build(
            cup: $cup,
            events: $events,
            group: $group,
        );

        $name = $command->name;
        $rows = array_values(array_filter(
            $table->rows,
            static fn (CupTableRow $row): bool => $name === null || str_contains(mb_strtolower($row->personName), $name),
        ));

        $table = $table->withRows($rows);

        return $this->assembler->toViewCupTableDto($table);
    }
}
