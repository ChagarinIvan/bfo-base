<?php
declare(strict_types=1);

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
