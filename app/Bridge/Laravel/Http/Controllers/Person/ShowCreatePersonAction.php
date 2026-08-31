<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\Club\LegacySearchClubDto;
use App\Application\Service\Club\ListLegacyClubs;
use App\Application\Service\Club\ListLegacyClubsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class ShowCreatePersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(ListLegacyClubsService $service): View
    {
        $clubs = $service->execute(new ListLegacyClubs(new LegacySearchClubDto()));

        /** @see /resources/views/persons/create.blade.php */
        return $this->view('persons.create', ['clubs' => $clubs]);
    }
}
