<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Services\CupEventsService;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use function array_map;
use function array_slice;
use function array_sum;
use function implode;
use function is_numeric;

final class ExportCupGroupTableAction extends BaseController
{
    use CupAction;

    public function __invoke(Cup $cup, string $group, CupEventsService $service): Response
    {
        $cupGroup = CupGroupFactory::fromId($group);
        $points = $service->calculateCup($cup, $service->getCupEvents((string) $cup->id), $cupGroup);
        $lines = [$cupGroup->name(), 'Место;ФИО;Год;Клуб;Очки'];
        $place = 1;
        foreach ($points as $personPoints) {
            /** @var CupEventPoint|null $first */
            $first = $personPoints[0] ?? null;
            if ($first === null || $first->protocolLine->person_id === null) {
                continue;
            }
            $sum = array_slice($personPoints, 0, $cup->events_count)
                |> (fn($x) => array_map(static fn(CupEventPoint $point): float => is_numeric($point->points) ? (float)$point->points : 0, $x,))
                |> array_sum(...)
            ;

            $line = $first->protocolLine;
            $lines[] = implode(';', [$place++, $line->getFullName(), $line->year ?? '', $line->club, $sum]);
        }

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $cup->name . '_' . $cupGroup->name() . '.csv"',
        ]);
    }
}
