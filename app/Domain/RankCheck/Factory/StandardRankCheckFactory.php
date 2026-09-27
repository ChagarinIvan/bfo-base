<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Factory;

use App\Domain\Auth\Impression;
use App\Domain\RankCheck\Exception\UnableToCreate;
use App\Domain\RankCheck\RankCheck;
use App\Domain\RankCheck\RankCheckStatus;
use App\Domain\Shared\Clock;
use function str_contains;
use function strtolower;

final readonly class StandardRankCheckFactory implements RankCheckFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(RankCheckInput $input): RankCheck
    {
        if ($input->content === '' || !str_contains(strtolower($input->extension), 'csv')) {
            throw new UnableToCreate();
        }

        $check = new RankCheck();
        $check->status = RankCheckStatus::Parsing;
        $check->source_path = $input->sourcePath;
        $check->created = $check->updated = new Impression($this->clock->now(), $input->userId);

        return $check;
    }
}
