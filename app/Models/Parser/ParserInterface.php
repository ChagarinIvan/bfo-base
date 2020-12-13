<?php

namespace App\Models\Parser;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface ParserInterface
{
    public function parse(UploadedFile $file): Collection;
    public function check(UploadedFile $file, string $type = null): bool;
}
