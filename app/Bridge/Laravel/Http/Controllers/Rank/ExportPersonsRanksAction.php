<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use function fclose;
use function fpassthru;

class ExportPersonsRanksAction extends BaseController
{
    use RankAction;

    public function __invoke(Filesystem $storage): StreamedResponse|RedirectResponse
    {
        $path = '/exports/ranks.csv';

        if (!$storage->exists($path)) {
            return $this->redirectTo404Error();
        }

        try {
            return response()->streamDownload(static function () use ($storage, $path): void {
                $stream = $storage->readStream($path);

                if ($stream === null) {
                    throw new RuntimeException('Cannot read file');
                }

                fpassthru($stream);
                fclose($stream);
            }, 'ranks.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable) {
            return $this->redirectToError();
        }
    }
}
