<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

class BackUrlService
{
    private const BACK_URLS_KEY = 'back_urls_key';
    private const BACK = 'back';
    private const ACTION = 'actions';

    public function __construct(private Session $sessionManager)
    {
    }

    public function pop(): string
    {
        $urls = $this->sessionManager->pull(self::BACK_URLS_KEY, []);
        $urls = new Collection($urls);
        $url = $urls->pop();
        $urls->push(self::BACK);
        $this->set($urls);
        return $url === null ? '' : $url;
    }

    public function push(string $url): void
    {
        $urls = $this->sessionManager->pull(self::BACK_URLS_KEY, []);
        $urls = new Collection($urls);
        if ($urls->last() !== self::BACK) {
            $urls = $urls->push($url);
        } else {
            $urls->pop();
        }
        $this->set($urls);
    }

    public function clean(): void
    {
        $this->set(new Collection());
    }

    public function setActualAction(string $action): void
    {
        $this->sessionManager->put(self::ACTION, $action);
    }

    public function getActualAction(): string
    {
        return $this->sessionManager->get(self::ACTION, '');
    }

    private function set(Collection $urls): void
    {
        $this->sessionManager->put(self::BACK_URLS_KEY, $urls->toArray());
    }
}
