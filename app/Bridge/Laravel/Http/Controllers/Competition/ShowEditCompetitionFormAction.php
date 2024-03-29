<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Application\Service\Competition\ViewCompetition;
use App\Application\Service\Competition\ViewCompetitionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use function compact;

final class ShowEditCompetitionFormAction extends BaseController
{
    use CompetitionAction;

    public function __invoke(string $id, ViewCompetitionService $service): View|RedirectResponse
    {
        try {
            $competition = $service->execute(new ViewCompetition($id));
        } catch (CompetitionNotFound) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/competitions/edit.blade.php */
        return $this->view('competitions.edit', compact('competition'));
    }
}
