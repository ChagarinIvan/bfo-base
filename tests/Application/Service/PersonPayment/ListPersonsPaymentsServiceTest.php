<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPayment;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\PersonPayment\PersonPaymentAssembler;
use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Application\Dto\PersonPayment\ViewPersonPaymentDto;
use App\Application\Service\PersonPayment\ListPersonsPayments;
use App\Application\Service\PersonPayment\ListPersonsPaymentsService;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\PersonPayment\PersonPaymentRepository;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListPersonsPaymentsServiceTest extends TestCase
{
    private ListPersonsPaymentsService $service;

    private MockObject&PersonPaymentRepository $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListPersonsPaymentsService(
            $this->payments = $this->createMock(PersonPaymentRepository::class),
            new PersonPaymentAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_returns_empty_list_when_no_payments(): void
    {
        $this->payments->expects($this->once())->method('byCriteria')->willReturn(new Collection());

        $result = $this->service->execute(new ListPersonsPayments(new SearchPersonPaymentsDto('1')));

        $this->assertSame([], $result);
    }

    #[Test]
    public function it_assembles_dtos_for_each_payment(): void
    {
        $payments = PersonPayment::factory()->count(2)->make(['person_id' => 1]);

        $this->payments->expects($this->once())->method('byCriteria')->willReturn($payments);

        $result = $this->service->execute(new ListPersonsPayments(new SearchPersonPaymentsDto('1')));

        $this->assertCount(2, $result);
        $this->assertInstanceOf(ViewPersonPaymentDto::class, $result[0]);
        $this->assertSame('1', $result[0]->personId);
    }
}
