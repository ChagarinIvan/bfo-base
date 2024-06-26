<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Event\Protocol;
use App\Models\Parser\ParserFactory;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Collection;
use RuntimeException;
use function file_get_contents;

readonly class ParserService
{
    public function __construct(private GroupsService $groupsService)
    {
    }

    public function parse(Protocol $protocol): Collection
    {
        $parser = ParserFactory::createProtocolParser(
            $protocol->content,
            $this->groupsService->getAllGroupsWithout()->pluck('name'),
            $protocol->extension,
        );

        return $parser->parse($protocol->content);
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
