<?php

namespace App\Services;

use App\Models\Parser\ParserFactory;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use RuntimeException;

class ParserService
{
    public function __construct(private readonly GroupsService $groupsService)
    {}

    /**
     * По протоколу определяет необходимый парсер
     * Парсер разбирает протокол на сырые массивы данных из строк
     * Из сырых строк наполняются модели ProtocolLine
     */
    public function parseProtocol(string $protocol, bool $needConvert, string $extension): Collection
    {
        $parser = ParserFactory::createProtocolParser(
            $protocol,
            $this->groupsService->getAllGroupsWithout()->pluck('name'),
            $extension
        );

        dump($parser);
        dump($needConvert);
        dump($extension);
        $res = $parser->parse($protocol, $needConvert);
        dd($res);
        return $res;
    }

    /**
     * По листу определяет необходимый парсер
     * Парсер разбирает протокол на сырые массивы данных из строк
     */
    public function parserRankList(string $list): Collection
    {
        return ParserFactory::createListParser($list)->parse($list);
    }

    /**
     * Скачиваем протокол с сайта обеларусь.нет
     * и делаем из него протокол, который дальше будем парсить
     * @throw RuntimeException
     */
    public function uploadProtocol(string $url): string
    {
        $pageContent = file_get_contents($url);
        $doc = new DOMDocument();
        @$doc->loadHTML($pageContent);
        $xpath = new DOMXpath($doc);
        $resultNode = $xpath->query('//div[@id="results-body"]');

        if ($resultNode->length === 0) {
            throw new RuntimeException('Отсутствуют результаты на странице!!');
        }

        return $doc->saveHTML($resultNode->item(0));
    }
}
