<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionDto;
use App\Application\Service\Competition\UpdateCompetitionInfo;
use App\Application\Service\Competition\UpdateCompetitionInfoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class UpdateCompetitionAction extends Controller
{
    public function __invoke(
        string $id,
        CompetitionDto $info,
        UpdateCompetitionInfoService $service,
        UserId $userId,
    ): RedirectResponse {
        $competition = $service->execute(new UpdateCompetitionInfo($id, $info, $userId));

        return $this->redirector->action(ShowCompetitionAction::class, [$competition->id]);
    }
}
