<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Application\Service\PersonPayment\ListPersonPayments;
use App\Application\Service\PersonPayment\ListPersonPaymentsService;
use Illuminate\Contracts\View\View;
use function compact;

final class ShowPersonPaymentsListAction extends AbstractPersonAction
{
    public function __invoke(string $personId, ListPersonPaymentsService $service): View
    {
        $payments = $service->execute(new ListPersonPayments(new SearchPersonPaymentsDto($personId)));

        return $this->view('persons.payments', compact('payments'));
    }
}
