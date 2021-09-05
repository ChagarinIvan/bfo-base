<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Parser\ParserFactory;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;

class ParserService
{
    /**
     * По протоколу определяет необходимый парсер
     * Парсер разбирает протокол на сырые массивы данных из строк
     * Из сырых строк наполняются модели ProtocolLine
     *
     * @param string $protocol
     * @param bool $needConvert
     * @return Collection
     * @throw Exception
     */
    public function parserProtocol(string $protocol, bool $needConvert): Collection
    {
        $parser = ParserFactory::createParser($protocol);
        return $parser->parse($protocol, $needConvert);
    }

    /**
     * Скачиваем протокол с сайта обеларусь.нет
     * и делаем из него протокол, который дальше будем парсить
     *
     * @param string $url
     * @return string
     * @throw Exception
     */
    public function uploadProtocol(string $url): string
    {
        $pageContent = file_get_contents($url);
        $doc = new DOMDocument();
        @$doc->loadHTML($pageContent);
        $xpath = new DOMXpath($doc);
        $resultNode = $xpath->query('//div[@id="results-body"]');
        if ($resultNode->length === 0) {
            throw new \RuntimeException('Отсутствуют результаты на странице!!');
        }
        return $doc->saveHTML($resultNode->item(0));
    }
}
