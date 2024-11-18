<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Service\Event\DownloadEventProtocol;
use App\Application\Service\Event\DownloadEventProtocolService;
use App\Application\Service\Event\Exception\EventNotFound;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class DownloadEventProtocolAction extends BaseController
{
    use EventAction;

    public function __invoke(string $id, DownloadEventProtocolService $service, ResponseFactory $response): Response|RedirectResponse
    {
        try {
            $protocol = $service->execute(new DownloadEventProtocol($id));
        } catch (EventNotFound) {
            return $this->redirectTo404Error();
        }

        return $response->make(
            content: $protocol->content,
            headers: [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $protocol->name . '.' . $protocol->extension . '"',
            ]
        );
    }
}
