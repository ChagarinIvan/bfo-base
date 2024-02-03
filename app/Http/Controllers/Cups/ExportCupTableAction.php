<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function array_keys;

class ExportCupTableAction extends AbstractCupAction
{
    public function __invoke(Cup $cup): BinaryFileResponse
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'tempfile');

        foreach ($cup->getCupType()->getGroups() as $group) {
            $cupEvents = $this->cupEventsService->getCupEvents($cup)->sortBy('event.date');
            $cupPoints = $this->cupEventsService->calculateCup($cup, $cupEvents, $group);

            $view = $this->view('cup.export.table', [
                'cup' => $cup,
                'cupEvents' => $cupEvents,
                'cupPoints' => $cupPoints,
                'persons' => Person::whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
            ]);

            File::put($tempFilePath, $view->render());
        }

        return response()
            ->download(file: $tempFilePath, name: "$cup->name.html")
            ->deleteFileAfterSend()
        ;
    }
}
