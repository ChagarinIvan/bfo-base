<?php

declare(strict_types=1);

namespace Tests\Application\Service\Club;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Club\ClubAssembler;
use App\Application\Service\Club\ListAllClubService;
use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use function array_map;

final class ListAllClubServiceTest extends TestCase
{
    #[Test]
    public function it_reads_all_clubs_from_the_repository_and_returns_options(): void
    {
        $firstClub = $this->club(1, 'First Club');
        $secondClub = $this->club(2, 'Second Club');

        /** @var MockObject&ClubRepository $repository */
        $repository = $this->createMock(ClubRepository::class);
        $repository->expects($this->once())
            ->method('all')
            ->willReturn(new Collection([$firstClub, $secondClub]));
        $repository->expects($this->never())->method('byCriteria');

        $service = new ListAllClubService(
            $repository,
            new ClubAssembler(new AuthAssembler),
        );

        $result = $service->execute();

        $this->assertSame([
            ['id' => '1', 'name' => 'First Club'],
            ['id' => '2', 'name' => 'Second Club'],
        ], array_map(static fn (object $option): array => [
            'id' => $option->id,
            'name' => $option->name,
        ], $result));
    }

    private function club(int $id, string $name): Club
    {
        $club = $this->createStub(Club::class);
        $club->method('__get')->willReturnMap([
            ['id', $id],
            ['name', $name],
        ]);

        return $club;
    }
}
