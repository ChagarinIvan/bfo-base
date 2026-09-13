<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Event\Protocol;
use App\Domain\Group\GroupRepository;
use App\Models\Parser\ParserFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use function trim;

readonly class ParserService
{
    public function __construct(private GroupRepository $groups)
    {
    }

    public function parse(Protocol $protocol): Collection
    {
        $parser = ParserFactory::createProtocolParser(
            $protocol->content,
            $this->groups->all()->pluck('normalize_name'),
            $protocol->extension,
        );

        Log::info('Parse class ' . $parser::class);

        return $parser->parse($protocol->content)->map(static function (array $line): array {
            $line['club'] = trim($line['club']);

            return $line;
        });
    }
}
