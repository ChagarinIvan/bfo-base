<?php

namespace App\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class PersonPromptRepository
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function findPersonsPrompts(Collection $preparedLines): Collection
    {
        return $this->db->table('persons_prompt')
            ->whereIn('prompt', $preparedLines)
            ->get();
    }
}
