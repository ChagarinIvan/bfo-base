<?php

namespace App\Console\Commands;

use App\Facades\System;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IdentAllProtocolLineCommand extends Command
{
    protected $signature = 'protocol-lines:ident';

    //стартует каждую мінуту
    public function handle(): void
    {
        Log::info('Start indent All not indented lines');
        if (System::isIdentRunning()) {
            Log::info('Finished. Indent is running');
        }
        try {
            System::startIdent();
            self::runIdent();
        } finally {
            System::stopIdent();
        }
        Log::info('Finish indent All not indented lines at: '.Carbon::now()->format('Y-m-d h:m:s'));
    }

    public static function runIdent(): void
    {
        $lines = ProtocolLine::wherePersonId(null)
            ->orderByDesc('id')
            ->get();

        Log::info("Has {$lines->count()} not indented lines");

        $indentService = new IdentService();
        foreach ($lines as $line) {
            $personId = $indentService->identPerson($line->prepared_line);
            if ($personId > 0) {
                $line->person_id = $personId;
                $line->save();
            }
            Log::info("{$line->lastname} {$line->firstname} -- {$personId}");
        }
    }
}
