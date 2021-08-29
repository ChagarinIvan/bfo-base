<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Parser\ParserFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ParserService
{
    /**
     * По протоколу определяет необходимый парсер
     * Парсер разбирает протокол на сырые массивы данных из строк
     * Из сырых строк наполняются модели ProtocolLine
     *
     * @param UploadedFile $protocol
     * @return Collection
     * @throw Exception
     */
    public function parserProtocol(UploadedFile $protocol): Collection
    {
        $parser = ParserFactory::createParser($protocol);
        return $parser->parse($protocol);
    }
}
