<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Services\CupEventsService;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;
use function array_map;
use function array_slice;
use function array_sum;
use function implode;
use function is_numeric;

final class ExportCupTableAction extends BaseController
{
    use CupAction;

    public function __invoke(Cup $cup, CupEventsService $service): Response
    {
        $lines = [];
        foreach ($cup->groups() as $group) {
            $events = $service->getCupEvents((string) $cup->id);
            $points = $service->calculateCup($cup, $events, $group);
            $lines[] = $group->name();
            $lines[] = 'Место;ФИО;Год;Клуб;Очки';
            $place = 1;
            foreach ($points as $personPoints) {
                $first = $personPoints[0] ?? null;
                if ($first === null || $first->protocolLine->person_id === null) {
                    continue;
                }
                $sum = array_slice($personPoints, 0, $cup->events_count)
                    |> (static fn($x): array => array_map(static fn(CupEventPoint $point): float => is_numeric($point->points) ? (float)$point->points : 0, $x, ))
                    |> array_sum(...)
                ;
                $line = $first->protocolLine;
                $lines[] = implode(';', [$place++, $line->getFullName(), $line->year ?? '', $line->club, $sum]);
            }
            $lines[] = '';
        }

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $cup->name . '.csv"',
        ]);
    }
}
