<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $person = Person::find(192);
        $protocolLine = ProtocolLine::find(147);
        $identService = new IdentService();
        $identPersonId = $identService->identPerson($protocolLine);
        if ($identPersonId !== 146) {
            $person->setPrompt($protocolLine->getIndentLine());
            $person->save();
        }
        $protocolLine->person_id = 146;
        $protocolLine->save();
    }
}
