<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cups;

use App\Domain\Person\Person;
use App\Models\Cup;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function array_keys;
use function sys_get_temp_dir;
use function tempnam;

class ExportCupTableAction extends AbstractCupAction
{
    public function __invoke(Cup $cup): BinaryFileResponse
    {
        $content = '';
        foreach ($cup->getCupType()->getGroups() as $group) {
            $cupEvents = $this->cupEventsService->getCupEvents($cup)->sortBy('event.date');
            $cupPoints = $this->cupEventsService->calculateCup($cup, $cupEvents, $group);

            $content .= "<b>{$group->name()}</b><br/><br/>";
            $content .= $this
                ->view('cup.export.table', [
                    'cup' => $cup,
                    'cupEvents' => $cupEvents,
                    'cupPoints' => $cupPoints,
                    'persons' => Person::whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
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
