<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Console\Commands\IdentProtocolLineCommand;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class IdentController extends Controller
{
    public function startIdent(): Response
    {
        IdentProtocolLineCommand::runIdent();
        return response('ok');
    }
}
