<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Application\Service\PersonPayment\ListPersonsPayments;
use App\Application\Service\PersonPayment\ListPersonsPaymentsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use function compact;

final class ShowPersonPaymentsListAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string                     $personId,
        ListPersonsPaymentsService $personPaymentsService,
        ViewPersonService          $personService,
    ): View|RedirectResponse {
        try {
            $person = $personService->execute(new ViewPerson($personId));
        } catch (PersonNotFound) {
            return $this->redirector->action(ShowPersonsListAction::class);
        }

        $payments = $personPaymentsService->execute(new ListPersonsPayments(new SearchPersonPaymentsDto($personId)));

        /** @see /resources/views/persons/payments.blade.php */
        return $this->view('persons.payments', compact('person', 'payments'));
    }
}
