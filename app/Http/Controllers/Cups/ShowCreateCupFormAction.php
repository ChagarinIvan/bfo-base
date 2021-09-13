<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Repositories\GroupsRepository;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;

class ShowCreateCupFormAction extends AbstractCupViewAction
{
    private GroupsRepository $groupsRepository;

    public function __construct(ViewActionsService $viewService, GroupsRepository $groupsRepository)
    {
        parent::__construct($viewService);
        $this->groupsRepository = $groupsRepository;
    }

    public function __invoke(int $year): View
    {
        return $this->view('cup.create', [
            'groups' => $this->groupsRepository->getAll(),
            'selectedYear' => $year,
        ]);
    }
}
