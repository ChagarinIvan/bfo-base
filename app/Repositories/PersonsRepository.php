<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class PersonsRepository
{
    public function __construct(private readonly ConnectionInterface $db)
    {}

    public function getPersonsList(int $limit, int $offset, string $sortBy, string $sortMode, string $search): QueryResult
    {
        $query = $this->db->table('person')
            ->selectRaw(
                new Expression('
                    person.id AS id,
                    ANY_VALUE(CONCAT(person.lastname, \' \', person.firstname)) AS `fio`,
                    COUNT(protocol_lines.id) AS `events_count`,
                    ANY_VALUE(club.name) AS `club_name`,
                    ANY_VALUE(person.club_id) AS `club_id`,
                    DATE_FORMAT(ANY_VALUE(person.birthday), \'%Y\') AS `birthday`
                ')
            )
            ->leftJoin('protocol_lines', 'person.id', '=', 'protocol_lines.person_id')
            ->leftJoin('club', 'person.club_id', '=', 'club.id')
            ->groupBy('person.id');

        if ($search !== '') {
            $query->where('person.firstname', 'like', "%{$search}%")
                ->orWhere('person.lastname', 'like', "%{$search}%")
                ->orWhere('club.name', 'like', "%{$search}%")
                ->orWhere('person.birthday', 'like', "%{$search}%");
        }

        $count = (int)$this->db->selectOne("SELECT COUNT(*) AS `count` FROM ({$query->toSql()}) tmp", $query->getBindings())->count;

        $query = $query
            ->orderBy($sortBy, $sortMode)
            ->limit($limit)
            ->offset($offset);

        $entities = $this->db->select($query->toSql(), $query->getBindings());

        return new QueryResult(new Collection($entities), $count);
    }
}
