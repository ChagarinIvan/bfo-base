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

final class ListPersonPaymentsServiceTest extends TestCase
{
    private MockObject&PersonPaymentRepository $payments;

    private ListPersonsPaymentsService $service;

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
    public function it_gets_list_of_person_payments(): void
    {
        $this->payments
            ->expects($this->once())
            ->method('paginate')
            ->willReturn(new Slice(new ArrayAdapter([
                $this->paymentMock(),
                $this->paymentMock(),
            ])))
        ;

        $list = $this->service->paginate(new ListPersonsPayments(new SearchPersonPaymentsDto(personId: '1')));

        $this->assertCount(2, $list);
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
