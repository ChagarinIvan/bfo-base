<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Service\Club\ListClubsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowCreatePersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(ListClubsService $service): View
    {
        $clubs = $service->execute();

        /** @see /resources/views/persons/create.blade.php */
        return $this->view('persons.create', compact('clubs'));
    }
}
