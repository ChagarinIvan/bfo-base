<?php
declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Controllers\AbstractAction;
use App\Services\ClubsService;
use App\Services\PersonsService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

abstract class AbstractClubAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        protected PersonsService $personsService,
        protected ClubsService $clubsService,
    ) {
        parent::__construct($viewService, $redirector);
    }

    protected function isClubsRoute(): bool
    {
        return true;
    }
}
