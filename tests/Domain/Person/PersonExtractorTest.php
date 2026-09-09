<?php

declare(strict_types=1);

namespace Tests\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Club\ClubRepository;
use App\Domain\Person\Citizenship;
use App\Domain\Person\PersonExtractor;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\SymbolNormalizer;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PersonExtractorTest extends TestCase
{
    #[Test]
    public function it_creates_a_belarusian_person_from_a_protocol_line(): void
    {
        $clubs = $this->createMock(ClubRepository::class);
        $clubs->expects($this->once())->method('oneByNormalizedName')->with('тэставы клуб')->willReturn($this->clubStub(42));

        $person = (new PersonExtractor($clubs, new ClubNameNormalizer(new SymbolNormalizer())))->extract(
            $this->protocolLineStub(),
            new Impression(Carbon::parse('2026-09-09 10:00:00'), 7),
        );

        $this->assertSame('Іваноў', $person->lastname);
        $this->assertSame('Ян', $person->firstname);
        $this->assertSame('2001-01-01', $person->birthday?->toDateString());
        $this->assertSame(42, $person->club_id);
        $this->assertSame(Citizenship::BELARUS, $person->citizenship);
        $this->assertFalse($person->from_base);
        $this->assertSame(7, $person->created->by);
        $this->assertSame('2026-09-09 10:00:00', $person->created->at->toDateTimeString());
    }

    private function protocolLineStub(): ProtocolLine
    {
        $line = $this->createStub(ProtocolLine::class);
        $line->method('__get')->willReturnMap([
            ['lastname', 'Іваноў'],
            ['firstname', 'Ян'],
            ['year', 2001],
            ['club', '  ТЭСТАВЫ КЛУБ  '],
        ]);

        return $line;
    }

    private function clubStub(int $id): Club
    {
        $club = $this->createStub(Club::class);
        $club->method('__get')->willReturnMap([['id', $id]]);

        return $club;
    }
}
