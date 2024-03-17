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
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListPersonPaymentsServiceTest extends TestCase
{
    private PersonPaymentRepository&MockObject $payments;

    private ListPersonsPaymentsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payments = $this->createMock(PersonPaymentRepository::class);
        $this->service = new ListPersonsPaymentsService($this->payments, new PersonPaymentAssembler(new AuthAssembler));
    }

    /** @test */
    public function it_gets_list_of_person_payments(): void
    {
        $this->payments
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1])))
            ->willReturn(PersonPayment::factory(2)->make())
        ;

        $list = $this->service->execute(new ListPersonsPayments(new SearchPersonPaymentsDto(personId: '1')));

        $this->assertCount(2, $list);
        $this->assertContainsOnlyInstancesOf(ViewPersonPaymentDto::class, $list);
    }
}
