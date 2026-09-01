<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Club\ClubRepository;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\SymbolNormalizer;
use App\Services\PersonsService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class PersonsServiceTest extends TestCase
{
    private ClubRepository&MockObject $clubs;

    #[Test]
    public function it_uses_the_shared_normalizer_when_extracting_a_person_from_a_protocol_line(): void
    {
        $club = $this->createStub(Club::class);
        $club->method('__get')->willReturnMap([['id', 7]]);
        $this->clubs = $this->createMock(ClubRepository::class);
        $this->clubs
            ->expects($this->once())
            ->method('oneByNormalizedName')
            ->with('бгу')
            ->willReturn($club);
        $service = new PersonsService(
            $this->clubs,
            new ClubNameNormalizer(new SymbolNormalizer),
        );
        $line = new ProtocolLine([
            'lastname' => 'Тэст',
            'firstname' => 'Спартсмен',
            'club' => '  BSU  ',
            'year' => 2000,
        ]);

        $person = $service->extractPersonFromLine(
            $line,
            new Impression(Carbon::parse('2026-09-01'), 1),
        );

        $this->assertSame(7, $person->club_id);
        $this->assertSame('Тэст', $person->lastname);
        $this->assertSame('Спартсмен', $person->firstname);
    }
}
