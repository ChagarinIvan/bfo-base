<?php

namespace App\Models\Parser;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface ParserInterface
{
    /**
     * @param UploadedFile $file
     * @return Collection
     * @throw \App\Exceptions\ParsingException
     */
    public function parse(UploadedFile $file): Collection;

    public function check(UploadedFile $file): bool;
}
