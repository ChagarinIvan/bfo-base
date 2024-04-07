<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Domain\Event\Protocol;
use DOMDocument;
use DOMXPath;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use function file_get_contents;

trait UploadHelper
{
    /**
     * Скачиваем протокол с сайта обеларусь.нет
     * и делаем из него протокол, который дальше будем парсить
     * @throw RuntimeException
     */
    public function uploadProtocol(string $url): Protocol
    {
        $pageContent = file_get_contents($url);
        $doc = new DOMDocument();
        @$doc->loadHTML($pageContent);
        $xpath = new DOMXpath($doc);
        $resultNode = $xpath->query('//div[@id="results-body"]');

        if ($resultNode->length === 0) {
            throw new BadRequestException('wrong protocol content');
        }

        $content = $doc->saveHTML($resultNode->item(0));

        return new Protocol(
            mb_convert_encoding($content, 'utf-8', 'windows-1251'),
            'html'
        );

    }
}
