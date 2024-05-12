<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Person\Person;
use App\Services\CupEventsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function array_filter;
use function array_keys;
use function count;
use function sys_get_temp_dir;
use function tempnam;

class ExportCupGroupTableAction extends BaseController
{
    use CupAction;

    public function __invoke(Cup $cup, string $cupGroupId, CupEventsService $service): BinaryFileResponse
    {
        $cupEvents = $service->getCupEvents((string) $cup->id)->sortBy('event.date');
        $cupGroup = CupGroupFactory::fromId($cupGroupId);
        $cupPoints = $service->calculateCup($cup, $cupEvents, $cupGroup);

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
