<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\CupEventPoint;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function array_filter;
use function array_keys;
use function count;
use function sys_get_temp_dir;
use function tempnam;

class ExportCupGroupTableAction extends AbstractCupAction
{
    public function __invoke(Cup $cup, string $cupGroupId): BinaryFileResponse
    {
        $cupEvents = $this->cupEventsService->getCupEvents($cup)->sortBy('event.date');
        $cupGroup = Group\CupGroupFactory::fromId($cupGroupId);
        $cupPoints = $this->cupEventsService->calculateCup($cup, $cupEvents, $cupGroup);

        // толькі тыя у каго бал большы за 0
        $cupPoints = array_filter(
            $cupPoints,
            static fn (array $points) => count(array_filter($points, static fn (CupEventPoint $p) => $p->points > 0)) > 0,
        );

        $view = $this->view('cup.export.table', [
            'cup' => $cup,
            'cupEvents' => $cupEvents,
            'cupPoints' => $cupPoints,
            'persons' => Person::whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
        ]);

        $tempFilePath = tempnam(sys_get_temp_dir(), 'tempfile');
        File::put($tempFilePath, $view->render());

        return response()
            ->download(file: $tempFilePath, name: "{$cup->name}_{$cupGroup->name()}.html")
            ->deleteFileAfterSend()
        ;
    }
}
