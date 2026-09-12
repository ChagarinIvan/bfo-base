<?php

declare(strict_types=1);

namespace App\Domain\Event\Protocol;

use App\Domain\Event\Exception\InvalidProtocolContent;
use App\Domain\Event\Protocol;
use DOMDocument;
use DOMXPath;
use function file_get_contents;

final class ProtocolFactory
{
    public function create(ProtocolSource $source): Protocol
    {
        if ($source->file !== null) {
            return new Protocol($source->file->getContent(), (string) $source->file->getMimeType());
        }

        $document = new DOMDocument;
        @$document->loadHTML((string) file_get_contents((string) $source->url));
        $xpath = new DOMXPath($document);
        $resultNode = $xpath->query('//div[@id="results-body"]');

        if ($resultNode === false || $resultNode->length === 0) {
            throw new InvalidProtocolContent('wrong protocol content');
        }

        return new Protocol($document->saveHTML($resultNode->item(0)), 'html');
    }
}
