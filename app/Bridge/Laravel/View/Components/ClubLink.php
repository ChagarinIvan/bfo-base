<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\View\Components;

use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\Exception\ClubNotFound;
use App\Application\Service\Club\ViewClub;
use App\Application\Service\Club\ViewClubService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class ClubLink extends Component
{
    public function __construct(
        private readonly ?string $clubId,
        private readonly ViewClubService $service,
        private readonly ?ViewClubDto $club = null,
    ) {
    }

    public function render(): View
    {
        $club = $this->club;

        if ($club === null && $this->clubId) {
            try {
                $club = $this->service->execute(new ViewClub($this->clubId));
            } catch (ClubNotFound) {
                $club = null;
            }
        }

        /** @see /resources/views/components/club-link.blade.php */
        return view('components.club-link', ['club' => $club]);
    }
}
