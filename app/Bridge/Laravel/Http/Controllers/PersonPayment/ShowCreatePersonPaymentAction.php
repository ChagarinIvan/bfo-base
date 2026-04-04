<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPayment;

use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Bridge\Laravel\Http\Controllers\Person\PersonAction;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowCreatePersonPaymentAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $personId,
        ViewPersonService $service,
    ): View {
        $person = $service->execute(new ViewPerson($personId));

        /** @see /resources/views/person-payment/create.blade.php */
        return $this->view('person-payment.create', compact('person'));
    }
}
