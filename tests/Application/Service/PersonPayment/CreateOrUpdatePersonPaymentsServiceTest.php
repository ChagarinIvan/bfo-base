<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPayment;

use App\Application\Dto\PersonPayment\PersonPaymentDto;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPayments;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPaymentsService;
use App\Domain\PersonPayment\PersonPaymentFactory;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Criteria;
use App\Models\PersonPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateOrUpdatePersonPaymentsServiceTest extends TestCase
{
    private PersonPaymentRepository&MockObject $payments;

    private CreateOrUpdatePersonPaymentsService $service;

    protected function setUp(): void
    {
        $this->payments = $this->createMock(PersonPaymentRepository::class);
        $this->service = new CreateOrUpdatePersonPaymentsService($this->payments, new PersonPaymentFactory());
    }

    /** @test */
    public function it_creates_new_payments(): void
    {
        $this->payments
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1, 'year' => 2021])))
            ->willReturn(Collection::make())
        ;

        $personPayment = new PersonPayment();
        $personPayment->person_id = 1;
        $personPayment->year = 2021;
        $personPayment->date = Carbon::createFromFormat('Y-m-d', '2021-01-01');

        $this->payments
            ->expects($this->once())
            ->method('add')
            ->with($this->equalTo($personPayment))
        ;

        $this->service->execute(new CreateOrUpdatePersonPayments(new PersonPaymentDto(
            personId: '1',
            year: '2021',
            date: '2021-01-01',
        )));
    }
}
