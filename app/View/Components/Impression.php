<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Application\Dto\Auth\ImpressionDto;
use App\Domain\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use function compact;

final class Impression extends Component
{
    public function __construct(
        private readonly ImpressionDto $impression,
        private readonly UserRepository $users,
    ) {
    }

    public function render(): View
    {
        $date = Carbon::parse($this->impression->at)->format('Y-m-d');
        $email = $this->users->byId((int) $this->impression->by)?->email ?? 'unknown';

        /** @see /resources/views/components/impression.blade.php */
        return view('components.impression', compact('date', 'email'));
    }
}
