<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Person\Person;
use App\Services\CupEventsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function array_keys;
use function sys_get_temp_dir;
use function tempnam;

class ExportCupTableAction extends BaseController
{
    use CupAction;

    public function __invoke(Cup $cup, CupEventsService $service): BinaryFileResponse
    {
        $content = '';
        foreach ($cup->type->instance()->getGroups() as $group) {
            $cupEvents = $service->getCupEvents((string) $cup->id)->sortBy('event.date');
            $cupPoints = $service->calculateCup($cup, $cupEvents, $group);

            $content .= "<b>{$group->name()}</b><br/><br/>";
            $content .= $this
                ->view('cup.export.table', [
                    'cup' => $cup,
                    'cupEvents' => $cupEvents,
                    'cupPoints' => $cupPoints,
                    'persons' => Person::where('active', true)->whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
                ])
                ->render()
            ;
            $content .= "<br/><br/>";
        }
        $tempFilePath = tempnam(sys_get_temp_dir(), 'tempfile');
        File::put($tempFilePath, $content);

        return response()
            ->download(file: $tempFilePath, name: "$cup->name.html")
            ->deleteFileAfterSend()
        ;
    }
}
