<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\UserService;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class ShowPersonAction extends AbstractPersonViewAction
{
    private UrlGenerator $urlGenerator;

    public function __construct(ViewFactory $viewFactory, UserService $userService, UrlGenerator $urlGenerator)
    {
        parent::__construct($viewFactory, $userService);
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(Person $person): View
    {
        /** fn features from php 7.4 */
        $groupedProtocolLines = $person->protocolLines->groupBy(fn (ProtocolLine $line) => $line->distance->event->date->format('Y'));
        $groupedProtocolLines->transform(function (Collection $protocolLines) {
            /** fn features from php 7.4 */
            return $protocolLines->sortByDesc(fn(ProtocolLine $line) => $line->distance->event->date);
        });
        $groupedProtocolLines = $groupedProtocolLines->sortKeysDesc();

        return $this->view('persons.show', [
            'person' => $person,
            'groupedProtocolLines' => $groupedProtocolLines,
            'backUrl' => $this->urlGenerator->previous(),
        ]);
    }
}
