<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPayment;

use App\Application\Dto\PersonPayment\PersonPaymentDto;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPayments;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPaymentsService;
use App\Domain\PersonPayment\PersonPaymentFactory;
use App\Domain\PersonPayment\PersonPaymentInput;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use App\Models\PersonPayment;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class CreateOrUpdatePersonPaymentsServiceTest extends TestCase
{
    private PersonPaymentRepository&MockObject $payments;

    private CreateOrUpdatePersonPaymentsService $service;

    private PersonPaymentFactory&MockObject $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payments = $this->createMock(PersonPaymentRepository::class);
        $this->factory = $this->createMock(PersonPaymentFactory::class);

        $this->service = new CreateOrUpdatePersonPaymentsService(
            $this->payments,
            $this->factory,
            new DummyTransactional(),
            new FrozenClock(),
        );
    }

    /** @test */
    public function it_creates_new_payments(): void
    {
        $this->payments
            ->expects($this->once())
            ->method('lockOneByCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1, 'year' => 2021])))
        ;

        $date = Carbon::createFromFormat('Y-m-d', '2021-01-01');
        $personPayment = PersonPayment::factory(state: ['year' => 2021,'date' => $date])->makeOne();

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with(new PersonPaymentInput(1, 2021, $date, 1))
            ->willReturn($personPayment)
        ;

        $this->payments->expects($this->never())->method('update');
        $this->payments
            ->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($personPayment))
        ;

        $this->service->execute(new CreateOrUpdatePersonPayments(
            new PersonPaymentDto('1', '2021', '2021-01-01'),
            1,
        ));
    }

    /** @test */
    public function it_doesnt_update_if_no_changes(): void
    {
        $date = Carbon::createFromFormat('Y-m-d', '2021-01-01');
        $personPayment = PersonPayment::factory(state: ['year' => 2021, 'date' => $date])->makeOne();

        $this->payments
            ->expects($this->once())
            ->method('lockOneByCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1, 'year' => 2021])))
            ->willReturn($personPayment)
        ;

        $this->factory->expects($this->never())->method('create');
        $this->payments->expects($this->never())->method('update');
        $this->payments->expects($this->never())->method('add');

        $this->service->execute(new CreateOrUpdatePersonPayments(
            new PersonPaymentDto('1', '2021', '2021-01-01'),
            1,
        ));
    }

    /** @test */
    public function it_updates_existed_payment(): void
    {
        /** @var PersonPayment $existPersonPayment */
        $existPersonPayment = PersonPayment::factory(state: [
            'id' => 1,
            'year' => 2021,
            'date' => Carbon::createFromFormat('Y-m-d', '2021-02-01'),
        ])->makeOne();

        $this->payments
            ->expects($this->once())
            ->method('lockOneByCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1, 'year' => 2021])))
            ->willReturn($existPersonPayment)
        ;

        $this->payments->expects($this->never())->method('add');
        $personPayment = PersonPayment::factory(state: [
            'id' => 1,
            'person_id' => $existPersonPayment->person_id,
            'year' => 2021,
            'date' => Carbon::createFromFormat('Y-m-d', '2021-01-01'),
        ])->makeOne();

        $this->payments
            ->expects($this->once())
            ->method('update')
        ;

        $this->service->execute(new CreateOrUpdatePersonPayments(
            new PersonPaymentDto('1', '2021', '2021-01-01'),
            1,
        ));
    }
}
