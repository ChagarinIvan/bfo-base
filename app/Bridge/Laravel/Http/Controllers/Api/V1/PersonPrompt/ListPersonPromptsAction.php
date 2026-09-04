<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt;

use App\Application\Dto\Pagination\Pagination;
use App\Application\Dto\PersonPrompt\SearchPersonPromptDto;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\ListPersonsPrompts;
use App\Application\Service\PersonPrompt\ListPersonsPromptsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListPersonPromptsAction extends BaseController
{
    use ApiAction;

    /** @return Slice<ViewPersonPromptDto> */
    public function __invoke(SearchPersonPromptDto $search, Pagination $pagination, ListPersonsPromptsService $service): Slice
    {
        return $service
            ->paginate(new ListPersonsPrompts($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
