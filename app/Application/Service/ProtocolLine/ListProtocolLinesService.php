<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Dto\ProtocolLine\ProtocolLineAssembler;
use App\Application\Dto\ProtocolLine\ViewProtocolLineDto;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Club\ClubRepository;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use function array_map;
use function array_unique;

final readonly class ListProtocolLinesService
{
    public function __construct(
        private ProtocolLineRepository $lines,
        private ClubRepository $clubs,
        private ClubNameNormalizer $clubNameNormalizer,
        private ProtocolLineAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewProtocolLineDto> */
    public function execute(ListProtocolLines $command): Slice
    {
        $resources = $command->resources();
        $lines = $this->lines->paginate($command->criteria(), $resources);

        if (!$resources->withClub) {
            return $lines->map(
                fn (ProtocolLine $line): ViewProtocolLineDto => $this->assembler->toViewProtocolLineDto($line),
            );
        }

        $clubsByNormalizedName = $this->clubs
            ->byCriteria(new Criteria([
                'normalizedNames' => array_map(static fn(ProtocolLine $line): string => $line->club, $lines->items())
                        |> (fn($x) => array_map($this->clubNameNormalizer->normalize(...), $x,))
                        |> array_unique(...),
            ]))
            ->keyBy('normalize_name')
        ;

        return $lines->map(function (ProtocolLine $line) use ($clubsByNormalizedName): ViewProtocolLineDto {
            $club = $clubsByNormalizedName->get($this->clubNameNormalizer->normalize($line->club));

            return $this->assembler->toViewProtocolLineDto($line, $club);
        });
    }
}
