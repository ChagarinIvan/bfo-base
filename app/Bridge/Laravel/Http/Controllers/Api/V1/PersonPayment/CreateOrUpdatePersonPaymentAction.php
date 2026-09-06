<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPayment;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPayment\PersonPaymentDto;
use App\Application\Dto\PersonPayment\ViewPersonPaymentDto;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPayments;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPaymentsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(201)]
final class CreateOrUpdatePersonPaymentAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        PersonPaymentDto $payment,
        CreateOrUpdatePersonPaymentsService $service,
        UserId $userId,
    ): ViewPersonPaymentDto {
        return $service->execute(new CreateOrUpdatePersonPayments($payment, $userId));
    }
}
