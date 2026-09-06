<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPayment;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\PersonPayment\PersonPaymentAssembler;
use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Application\Service\PersonPayment\ListPersonsPayments;
use App\Application\Service\PersonPayment\ListPersonsPaymentsService;
use App\Domain\Auth\Impression;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Pagination\Slice;
use Carbon\Carbon;
use Pagerfanta\Adapter\ArrayAdapter;
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

        $this->payments = $this->createMock(PersonPaymentRepository::class);
        $this->service = new ListPersonsPaymentsService(
            $this->payments,
            new PersonPaymentAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_returns_empty_list_when_no_payments(): void
    {
        $this->payments
            ->expects($this->once())
            ->method('paginate')
            ->willReturn(new Slice(new ArrayAdapter([])))
        ;

        $result = $this->service->paginate(new ListPersonsPayments(new SearchPersonPaymentsDto('1')));

        $this->assertSame([], $result->items());
    }

    #[Test]
    public function it_assembles_dtos_for_each_payment(): void
    {
        $this->payments
            ->expects($this->once())
            ->method('paginate')
            ->willReturn(new Slice(new ArrayAdapter([$this->paymentMock(), $this->paymentMock()])))
        ;

        $result = $this->service->paginate(new ListPersonsPayments(new SearchPersonPaymentsDto('1')));

        $items = $result->items();
        $this->assertCount(2, $items);
        $this->assertSame('1', $items[0]->personId);
    }

    private function paymentMock(): PersonPayment
    {
        $payment = $this->createStub(PersonPayment::class);
        $payment->method('__get')->willReturnMap([
            ['id', 1],
            ['person_id', 1],
            ['year', 2025],
            ['date', Carbon::createFromFormat('Y-m-d', '2025-01-01')],
            ['created', new Impression(Carbon::now(), 1)],
            ['updated', new Impression(Carbon::now(), 1)],
        ]);

        return $payment;
    }
}
