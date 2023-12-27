<?php

declare(strict_types=1);

namespace App\Http\Controllers\Groups;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitGroupsAction extends AbstractGroupAction
{
    /**
     * @param int $firstGroupId //которую объединяем, в итоге она удаляется.
     *
     * @see resources/views/groups/show.blade.php
     */
    public function __invoke(Request $request, string $firstGroupId): RedirectResponse
    {
        $firstGroup = $this->groupsService->getGroup((int) $firstGroupId);
        $secondGroup = $this->groupsService->getGroup((int)$request->get('group_id'));

        foreach ($firstGroup->distances as $distance) {
            $this->distanceService->updateDistanceGroup($distance, $secondGroup->id);
        }
        $this->groupsService->deleteGroup($firstGroup);

        return $this->redirector->action(ShowGroupsListAction::class);
    }
}
