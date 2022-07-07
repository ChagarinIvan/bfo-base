<?php

namespace App\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class PersonPromptRepository
{
    public function __construct(private readonly ConnectionInterface $db)
    {}

    /**
     * @param string[] $preparedLines
     */
    public function findPersonsPrompts(array $preparedLines): Collection
    {
        return $this->db->table('persons_prompt')
            ->whereIn('prompt', $preparedLines)
            ->get();
    }
}
