<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Application\Service\PersonPayment\ListPersonPayments;
use App\Application\Service\PersonPayment\ListPersonPaymentsService;
use App\Http\Controllers\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

final class ShowPersonPaymentsListAction extends BaseController
{
    use Action, PersonAction {
        PersonAction::isPersonsRoute insteadof Action;
    }

    public function __invoke(
        string $personId,
        ListPersonPaymentsService $personPaymentsService,
        ViewPersonService $personService,
    ): View {
        $person = $personService->execute(new ViewPerson($personId));
        $payments = $personPaymentsService->execute(new ListPersonPayments(new SearchPersonPaymentsDto($personId)));

        return $this->view('persons.payments', compact('person', 'payments'));
    }
}
