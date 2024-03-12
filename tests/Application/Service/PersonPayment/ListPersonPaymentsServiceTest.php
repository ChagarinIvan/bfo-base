<?php

declare(strict_types=1);

namespace Application\Service\PersonPayment;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\PersonPayment\PersonPaymentAssembler;
use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Application\Dto\PersonPayment\ViewPersonPaymentDto;
use App\Application\Service\PersonPayment\ListPersonPayments;
use App\Application\Service\PersonPayment\ListPersonPaymentsService;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Criteria;
use App\Models\PersonPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ListPersonPaymentsServiceTest extends TestCase
{
    private PersonPaymentRepository&MockObject $payments;

    private ListPersonPaymentsService $service;

    protected function setUp(): void
    {
        $this->payments = $this->createMock(PersonPaymentRepository::class);
        $this->service = new ListPersonPaymentsService($this->payments, new PersonPaymentAssembler(new AuthAssembler));
    }

    /** @test */
    public function it_gets_list_of_person_payments(): void
    {
        $this->payments
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1])))
            ->willReturn(Collection::make([
                $this->createPersonPayment(1, 1, 2021, '2021-01-01'),
                $this->createPersonPayment(2, 1, 2022, '2022-01-01'),
            ]))
        ;

        $list = $this->service->execute(new ListPersonPayments(new SearchPersonPaymentsDto(personId: '1')));

        $this->assertCount(2, $list);
        $this->assertContainsOnlyInstancesOf(ViewPersonPaymentDto::class, $list);
    }

    private function createPersonPayment(int $id, int $personId, int $year, string $date): PersonPayment
    {
        $payment = new PersonPayment;
        $payment->id = $id;
        $payment->person_id = $personId;
        $payment->year = $year;
        $payment->date = new Carbon($date);

        return $payment;
    }
}
