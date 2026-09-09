<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Person\Person;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use function in_array;

class PersonsService
{
    private const array SORT_BY_COLUMNS = [
        'fio',
        'events_count',
        'club_name',
        'birthday',
    ];

    public function getPerson(int $personId): Person
    {
        return Person::where('active', true)->find($personId) ?? throw new RuntimeException('Wrong person id.');
    }

    /**
     * Выборка для страницы спортсменов для фронтенд апи с пагинацией и уже преобразованными полями.
     */
    public function getPersonsList(?string $sortBy = null, ?int $sortMode = null, ?string $search = null): Builder
    {
        $sortBy = in_array($sortBy, self::SORT_BY_COLUMNS, true) ? $sortBy : 'fio';
        $sort = $sortMode === 1 ? 'desc' : 'asc';

        $persons = Person::where('person.active', true)->withCount('protocolLines')->with('club');

        $persons = match ($sortBy) {
            'fio' => $persons->orderBy('lastname', $sort)->orderBy('firstname', $sort),
            'club_name' => $persons->join('club', 'person.club_id', '=', 'club.id')->orderBy('club.name', $sort),
            'events_count' => $persons->orderBy('protocol_lines_count', $sort),
            'birthday' => $persons->orderBy('birthday', $sort),
        };

        if ($search) {
            return $persons->where(static function ($query) use ($search): void {
                $query->where('firstname', 'like', "%$search%")
                    ->orWhere('lastname', 'like', "%$search%")
                    ->orWhere(DB::raw("CONCAT(`lastname`, ' ', `firstname`)"), 'like', "%$search%")
                    ->orWhereHas('club', static function ($query) use ($search): void {
                        $query->where('name', 'like', "%$search%");
                    })
                    ->orWhere('birthday', 'like', "%$search%");
            });
        }

        return $persons;
    }
}
