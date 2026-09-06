<?php

declare(strict_types=1);

namespace Tests\Application\Service\PersonPayment;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPayment\PersonPaymentAssembler;
use App\Application\Dto\PersonPayment\PersonPaymentDto;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPayments;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPaymentsService;
use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPayment\Factory\PersonPaymentFactory;
use App\Domain\PersonPayment\Factory\PersonPaymentInput;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Tests\TestCase;

final class CreateOrUpdatePersonPaymentsServiceTest extends TestCase
{
    private MockObject&PersonPaymentRepository $payments;

    private CreateOrUpdatePersonPaymentsService $service;

    private MockObject&PersonPaymentFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payments = $this->createMock(PersonPaymentRepository::class);
        $this->factory = $this->createMock(PersonPaymentFactory::class);
        $persons = $this->createStub(PersonRepository::class);
        $persons->method('byId')->willReturn($this->createStub(Person::class));

        $this->service = new CreateOrUpdatePersonPaymentsService(
            $this->payments,
            $this->factory,
            new PersonPaymentAssembler(new AuthAssembler),
            $persons,
            new DummyTransactional(),
            new FrozenClock(),
        );
    }

    #[Test]
    public function it_creates_new_payments(): void
    {
        $this->payments
            ->expects($this->once())
            ->method('lockOneByCriteria')
            ->with(new Criteria(['personId' => 1, 'year' => 2021]))
        ;

        $date = Carbon::createFromFormat('Y-m-d', '2021-01-01');
        $personPayment = $this->paymentStub();

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

        $dto = new PersonPaymentDto();
        $dto->personId = '1';
        $dto->date = '2021-01-01';

        $this->service->execute(new CreateOrUpdatePersonPayments($dto, new UserId(1)));
    }

    #[Test]
    public function it_doesnt_update_if_no_changes(): void
    {
        $personPayment = $this->paymentStub();
        $personPayment->method('sameDate')->willReturn(true);

        $this->payments
            ->expects($this->once())
            ->method('lockOneByCriteria')
            ->with(new Criteria(['personId' => 1, 'year' => 2021]))
            ->willReturn($personPayment)
        ;

        $this->factory->expects($this->never())->method('create');
        $this->payments->expects($this->never())->method('update');
        $this->payments->expects($this->never())->method('add');

        $dto = new PersonPaymentDto();
        $dto->personId = '1';
        $dto->date = '2021-01-01';

        $this->service->execute(new CreateOrUpdatePersonPayments($dto, new UserId(1)));
    }

    #[Test]
    public function it_updates_existed_payment(): void
    {
        $date = Carbon::createFromFormat('Y-m-d', '2021-01-01');
        $existPersonPayment = $this->paymentMock();
        $existPersonPayment
            ->expects($this->once())
            ->method('sameDate')
            ->with($date)
            ->willReturn(false)
        ;
        $existPersonPayment->expects($this->once())->method('updateDate');

        $this->payments
            ->expects($this->once())
            ->method('lockOneByCriteria')
            ->with(new Criteria(['personId' => 1, 'year' => 2021]))
            ->willReturn($existPersonPayment)
        ;

        $this->payments->expects($this->never())->method('add');
        // при обновлении существующего платежа фабрика создания не задействуется
        $this->factory->expects($this->never())->method($this->anything());
        $this->payments
            ->expects($this->once())
            ->method('update')
        ;

        $dto = new PersonPaymentDto();
        $dto->personId = '1';
        $dto->date = '2021-01-01';

        $this->service->execute(new CreateOrUpdatePersonPayments($dto, new UserId(1)));
    }

    private function paymentMock(): MockObject&PersonPayment
    {
        $payment = $this->getMockBuilder(PersonPayment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get', 'sameDate', 'updateDate'])
            ->getMock()
        ;
        $payment->method('__get')->willReturnMap([
            ['id', 1],
            ['person_id', 1],
            ['year', 2021],
            ['date', Carbon::createFromFormat('Y-m-d', '2021-01-01')],
            ['created', new Impression(Carbon::now(), 1)],
            ['updated', new Impression(Carbon::now(), 1)],
        ]);

        return $payment;
    }

    private function paymentStub(): PersonPayment&Stub
    {
        $payment = $this->createStub(PersonPayment::class);
        $payment->method('__get')->willReturnMap([
            ['id', 1],
            ['person_id', 1],
            ['year', 2021],
            ['date', Carbon::createFromFormat('Y-m-d', '2021-01-01')],
            ['created', new Impression(Carbon::now(), 1)],
            ['updated', new Impression(Carbon::now(), 1)],
        ]);

        return $payment;
    }
}
