<?php

namespace App\Repositories;

use App\Models\Cup;

class CupsRepository
{
    public function getCup(int $id): ?Cup
    {
        return Cup::find($id);
    }
}
