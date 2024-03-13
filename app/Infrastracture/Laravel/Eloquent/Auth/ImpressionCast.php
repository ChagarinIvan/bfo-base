<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Auth;

use App\Domain\Auth\Impression;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

final readonly class ImpressionCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): Impression
    {
        return new Impression(
            at: Carbon::parse($attributes["{$key}_at"]),
            by: (int) $attributes["{$key}_by"],
        );
    }

    /**
     * @param Impression $value
     * @param mixed $model
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        return [
            "{$key}_at" => $value->at,
            "{$key}_by" => $value->by,
        ];
    }
}
