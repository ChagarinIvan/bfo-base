<?php

namespace App\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitGroupsAction extends AbstractGroupAction
{
    /**
     * @param Request $request
     * @param int $firstGroupId //которую объединяем, в итоге она удаляется.
     * @return View|RedirectResponse
     * @template resources/views/groups/show.blade.php
     */
    public function __invoke(Request $request, int $firstGroupId): View|RedirectResponse
    {
        $firstGroup = $this->groupsService->getGroup($firstGroupId);
        $secondGroup = $this->groupsService->getGroup((int)$request->get('group_id'));

        if ($firstGroup === null || $secondGroup === null) {
            return $this->redirectToError();
        }

        foreach ($firstGroup->distances as $distance) {
            $this->distanceService->updateDistanceGroup($distance, $secondGroup->id);
        }
        $this->groupsService->deleteGroup($firstGroup);

        return $this->redirector->action(ShowGroupsListAction::class);
    }
}
