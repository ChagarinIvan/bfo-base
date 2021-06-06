<?php

namespace App\Console\Commands;

use App\Facades\System;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Console\Command;

class IdentProtocolLineCommand extends Command
{
    protected $signature = 'protocol-lines:ident';

    public function handle(): void
    {
        echo time().PHP_EOL;
        if (!System::isNeedRecheck()) {
            return;
        }

        System::startIdent();
        System::setNeedRecheck(false);
        self::runIdent();
        System::stopIdent();
        echo time().PHP_EOL;
    }

    public static function runIdent(): void
    {
        $lines = ProtocolLine::wherePersonId(null)
            ->get();

        $indentService = new IdentService();
        foreach ($lines as $line) {
            $personId = $indentService->identPerson($line->prepared_line);
            if ($personId > 0) {
                $line->person_id = $personId;
                $line->save();
            }
        }
    }
}
