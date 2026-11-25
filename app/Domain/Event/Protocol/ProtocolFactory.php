<?php

declare(strict_types=1);

namespace App\Domain\Event\Protocol;

use App\Domain\Event\Exception\InvalidProtocolContent;
use App\Domain\Event\Protocol;
use DOMDocument;
use DOMXPath;
use const LIBXML_NONET;

final readonly class ProtocolFactory
{
    public function __construct(private ProtocolUrlReader $reader)
    {
    }

    public function create(ProtocolSource $source): Protocol
    {
        if ($source->file !== null) {
            return new Protocol($source->file->getContent(), (string) $source->file->getMimeType());
        }

        $document = new DOMDocument;
        $content = $this->reader->read($source->url ?? '');
        if ($content === '' || !@$document->loadHTML($content, LIBXML_NONET)) {
            throw new InvalidProtocolContent('wrong protocol content');
        }
        $xpath = new DOMXPath($document);
        $resultNode = $xpath->query('//div[@id="results-body"]');

        if ($resultNode === false || $resultNode->length === 0) {
            throw new InvalidProtocolContent('wrong protocol content');
        }

        return new Protocol($document->saveHTML($resultNode->item(0)), 'html');
    }
}
