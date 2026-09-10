<?php

declare(strict_types=1);

namespace Tests\Application\Service\ProtocolLine;

use App\Application\Dto\ProtocolLine\ProtocolLineAssembler;
use App\Application\Dto\ProtocolLine\SearchProtocolLineDto;
use App\Application\Service\ProtocolLine\ListProtocolLines;
use App\Application\Service\ProtocolLine\ListProtocolLinesService;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Club\ClubRepository;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\ProtocolLine\ProtocolLineResources;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Domain\Shared\SymbolNormalizer;
use Pagerfanta\Adapter\ArrayAdapter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListProtocolLinesServiceTest extends TestCase
{
    #[Test]
    public function it_passes_criteria_and_resources_to_the_repository(): void
    {
        $lines = $this->createMock(ProtocolLineRepository::class);
        $lines->expects($this->once())
            ->method('paginate')
            ->with(
                new Criteria([
                    'personId' => '7',
                    'year' => '2026',
                    'competitionName' => 'Spring',
                    'date' => '2026-05-10',
                ]),
                new ProtocolLineResources(withEvent: true, withCompetition: true),
            )
            ->willReturn(new Slice(new ArrayAdapter([])))
        ;

        $service = new ListProtocolLinesService(
            $lines,
            $this->createStub(ClubRepository::class),
            new ClubNameNormalizer(new SymbolNormalizer()),
            new ProtocolLineAssembler(),
        );

        $result = $service->execute(new ListProtocolLines(new SearchProtocolLineDto(
            personId: '7',
            withEvent: '1',
            withCompetition: '1',
            year: '2026',
            competitionName: 'Spring',
            date: '2026-05-10',
        )));

        $this->assertInstanceOf(Slice::class, $result);
    }

    #[Test]
    public function it_returns_an_empty_slice_when_the_person_has_no_lines(): void
    {
        $lines = $this->createMock(ProtocolLineRepository::class);
        $lines->expects($this->once())
            ->method('paginate')
            ->with(new Criteria(['personId' => '7']), new ProtocolLineResources())
            ->willReturn(new Slice(new ArrayAdapter([])))
        ;

        $service = new ListProtocolLinesService(
            $lines,
            $this->createStub(ClubRepository::class),
            new ClubNameNormalizer(new SymbolNormalizer()),
            new ProtocolLineAssembler(),
        );

        $result = $service->execute(new ListProtocolLines(new SearchProtocolLineDto(personId: '7')));

        $this->assertCount(0, $result);
    }
}
