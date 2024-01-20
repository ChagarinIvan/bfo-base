<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\Group;
use App\Models\Person;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function array_keys;

class ShowCupTableExportAction extends AbstractCupAction
{
    public function __invoke(Cup $cup, string $cupGroupId): BinaryFileResponse
    {
        $cupEvents = $this->cupEventsService->getCupEvents($cup)->sortBy('event.date');
        $cupGroup = Group\CupGroupFactory::fromId($cupGroupId);
        $cupPoints = $this->cupEventsService->calculateCup($cup, $cupEvents, $cupGroup);

        $view = $this->view('cup.export.table', [
            'cup' => $cup,
            'cupEvents' => $cupEvents,
            'cupPoints' => $cupPoints,
            'persons' => Person::whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
        ]);

        $filename = tmpfile();
        fwrite($filename, $view->render());

        return response()->download($filename);
    }
}
