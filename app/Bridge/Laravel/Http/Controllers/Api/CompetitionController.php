<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api;

use App\Domain\Competition\Competition;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class CompetitionController extends BaseController
{
    public function index(): JsonResponse
    {
        $allCompetitions = Competition::all()->makeHidden(['created_at', 'updated_at']);
        $groupedCompetitions = $allCompetitions->sortByDesc(static fn (Competition $competition) => $competition->from->format('Y-m-d'));

        return response()->json($groupedCompetitions);
    }
}
