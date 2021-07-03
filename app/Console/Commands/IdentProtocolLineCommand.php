<?php

namespace App\Console\Commands;

use App\Models\IdentLine;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Console\Command;

/**
 * Будем определять людей из очереди на определение.
 * 2 человека в минуту
 * независимо от результата запись удаляется из очереди
 *
 * * * * * php /var/www/blog/artisan protocol-lines:queue-ident
 * * * * * (sleep 30; php /var/www/blog/artisan protocol-lines:queue-ident)
 *
 * Class IdentProtocolLineCommand
 * @package App\Console\Commands
 */
class IdentProtocolLineCommand extends Command
{
    protected $signature = 'protocol-lines:queue-ident';

    //стартует каждую минуту
    public function handle(): void
    {
        $identLine = IdentLine::first();
        $identLine->delete();

        $personId = (new IdentService())->identPerson($identLine->ident_line);
        if ($personId > 0) {
            $protocolLines = ProtocolLine::wherePreparedLine($identLine->ident_line);
            $protocolLines->each(function (ProtocolLine $protocolLine) use ($personId) {
                $protocolLine->person_id = $personId;
                $protocolLine->save();
            });
        }
    }
}
