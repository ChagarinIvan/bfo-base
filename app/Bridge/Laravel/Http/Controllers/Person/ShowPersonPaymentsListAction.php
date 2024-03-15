<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Application\Service\PersonPayment\ListPersonPayments;
use App\Application\Service\PersonPayment\ListPersonPaymentsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

final class ShowPersonPaymentsListAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $personId,
        ListPersonPaymentsService $personPaymentsService,
        ViewPersonService $personService,
    ): View {
        $person = $personService->execute(new ViewPerson($personId));
        $payments = $personPaymentsService->execute(new ListPersonPayments(new SearchPersonPaymentsDto($personId)));

        /** @see /resources/views/persons/payments.blade.php */
        return $this->view('persons.payments', compact('person', 'payments'));
    }
}