<?php

namespace App\Models\Parser;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface ParserInterface
{
    /**
     * @param UploadedFile $file
     * @return Collection
     */
    public function parse(UploadedFile $file): Collection;
    public function check(UploadedFile $file, string $type = null): bool;
}
