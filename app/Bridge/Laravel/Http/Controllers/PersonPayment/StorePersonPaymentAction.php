<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPayment;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPayment\PersonPaymentDto;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPayments;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPaymentsService;
use App\Bridge\Laravel\Http\Controllers\Person\PersonAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class StorePersonPaymentAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        PersonPaymentDto $dto,
        CreateOrUpdatePersonPaymentsService $service,
        UserId $userId,
    ): RedirectResponse {
        $payment = $service->execute(new CreateOrUpdatePersonPayments($dto, $userId));

        return $this->redirector->action(ShowPersonPaymentsListAction::class, [$payment->person_id]);
    }
}
