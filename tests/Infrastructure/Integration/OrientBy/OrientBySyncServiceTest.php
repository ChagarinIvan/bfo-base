<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Integration\OrientBy;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPaymentsService;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Club\ClubRepository;
use App\Domain\Person\Person;
use App\Domain\PersonPayment\Factory\PersonPaymentFactory;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Clock;
use App\Domain\Shared\SymbolNormalizer;
use App\Domain\Shared\TransactionManager;
use App\Infrastructure\Integration\OrientBy\OrientByPersonDto;
use App\Infrastructure\Integration\OrientBy\OrientBySyncService;
use App\Services\PersonsIdentService;
use App\Services\PersonsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\LogManager;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

final class OrientBySyncServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_ignores_external_rank_without_a_protocol_line(): void
    {
        /** @var Person $person */
        $person = Person::factory()->createOne([
            'id' => 1,
            'firstname' => 'Ivan',
            'lastname' => 'Ivanov',
            'current_rank' => Rank::FirstRank,
            'current_rank_started_on' => '2026-01-01',
            'current_rank_activated_on' => '2026-01-01',
            'current_rank_finished_on' => '2028-01-01',
        ]);
        $ident = $this->createMock(PersonsIdentService::class);
        $ident->expects($this->once())
            ->method('identLines')
            ->willReturn([PersonsIdentService::makeIdentLine('Ivanov', 'Ivan', 2000) => $person->id]);
        $persons = $this->createMock(PersonsService::class);
        $persons->expects($this->once())->method('getPerson')->with($person->id)->willReturn($person);
        $clock = $this->createStub(Clock::class);
        $clock->method('now')->willReturn(Carbon::parse('2026-09-01'));

        $this->service($ident, $persons, $clock)->synchronize([
            new OrientByPersonDto('Ivanov Ivan', 2000, null, 'МСМК', false, null),
        ], new UserId(1));

        $person->refresh();
        $this->assertSame(Rank::FirstRank, $person->current_rank);
        $this->assertSame('2026-01-01', $person->current_rank_started_on?->toDateString());
        self::assertDatabaseCount('person_rank_histories', 0);
    }

    private function service(PersonsIdentService $ident, PersonsService $persons, Clock $clock): OrientBySyncService
    {
        $logger = $this->createStub(LoggerInterface::class);
        $logManager = $this->createMock(LogManager::class);
        $logManager->expects($this->once())->method('channel')->with('sync')->willReturn($logger);

        return new OrientBySyncService(
            $ident,
            $persons,
            $this->createStub(ClubRepository::class),
            new ClubNameNormalizer(new SymbolNormalizer()),
            new CreateOrUpdatePersonPaymentsService(
                $this->createStub(PersonPaymentRepository::class),
                $this->createStub(PersonPaymentFactory::class),
                $this->createStub(TransactionManager::class),
                $clock,
            ),
            $clock,
            $logManager,
        );
    }
}
