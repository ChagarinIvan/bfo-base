<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Repositories\GroupsRepository;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;

class ShowEditCupFormAction extends AbstractCupViewAction
{
    private GroupsRepository $groupsRepository;

    public function __construct(ViewActionsService $viewService, GroupsRepository $groupsRepository)
    {
        parent::__construct($viewService);
        $this->groupsRepository = $groupsRepository;
    }

    public function __invoke(Cup $cup): View
    {
        return $this->view('cup.edit', [
            'cup' => $cup,
            'groups' => $this->groupsRepository->getAll(),
        ]);
    }
}
