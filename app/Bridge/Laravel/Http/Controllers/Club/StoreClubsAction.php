<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Club\ClubDto;
use App\Application\Service\Club\AddClub;
use App\Application\Service\Club\AddClubService;
use App\Application\Service\Club\Exception\FailedToAddClub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\View\View;

class StoreClubsAction extends BaseController
{
    use ClubAction;

    public function __invoke(
        ClubDto $clubDto,
        AddClubService $service,
        UserId $userId,
    ): View|RedirectResponse {
        try {
            $club = $service->execute(new AddClub($clubDto, $userId));
        } catch (FailedToAddClub) {
            return $this->redirectToError();
        }

        return $this->redirector->action(ShowClubAction::class, [$club->id]);
    }
}
