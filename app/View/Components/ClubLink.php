<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Application\Service\Club\Exception\ClubNotFound;
use App\Application\Service\Club\ViewClub;
use App\Application\Service\Club\ViewClubService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use function compact;

final class ClubLink extends Component
{
    public function __construct(
        private readonly ?string $clubId,
        private readonly ViewClubService $service,
    ) {
    }

    public function render(): View
    {
        if ($this->clubId) {
            try {
                $club = $this->service->execute(new ViewClub($this->clubId));
            } catch (ClubNotFound) {
                $club = null;
            }
        } else {
            $club = null;
        }

        /** @see /resources/views/components/club-link.blade.php */
        return view('components.club-link', compact('club'));
    }
}
