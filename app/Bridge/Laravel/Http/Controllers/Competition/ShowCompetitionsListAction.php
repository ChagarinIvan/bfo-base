<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Dto\Competition\CompetitionAssembler;
use App\Models\Year;
use Illuminate\Contracts\View\View;
use function array_map;

final class ShowCompetitionsListAction extends AbstractCompetitionAction
{
    public function __invoke(string $yearInput, CompetitionAssembler $assembler): View
    {
        $year = Year::from((int) $yearInput);
        $competitions = $this->competitionService->getYearCompetitions($year)->all();

        /** @see /resources/views/competitions/index.blade.php */
        return $this->view('competitions.index', [
            'competitions' => array_map($assembler->toViewCompetitionDto(...), $competitions),
            'selectedYear' => $year,
        ], Year::actualYear() === $year);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
