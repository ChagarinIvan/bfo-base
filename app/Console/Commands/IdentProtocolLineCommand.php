<?php

namespace App\Console\Commands;

use App\Facades\System;
//use App\Models\Group;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Console\Command;
//use Illuminate\Support\Collection;

class IdentProtocolLineCommand extends Command
{
    protected $signature = 'protocol-lines:ident';

    public function handle(): void
    {
        if (!System::needRecheck()) {
            return;
        }

//        /** @var Collection|ProtocolLine[] $lines */
//        $personsGroupIds = Group::where('name', 'NOT LIKE', "%10")
//            ->where('name', 'NOT LIKE', "%12")
//            ->where('name', 'NOT LIKE', "%14")
//            ->where('name', 'NOT LIKE', "%16")
//            ->get('id');

        $lines = ProtocolLine::wherePersonId(null)
//            ->whereIn('group_id', $personsGroupIds)
            ->get();

        $indentService = new IdentService();
        foreach ($lines as $line) {
            $personId = $indentService->identPerson($line);
            if ($personId > 0) {
                $line->person_id = $personId;
                $line->save();
            }
        }

        System::setNeedRecheck(false);
    }
}
