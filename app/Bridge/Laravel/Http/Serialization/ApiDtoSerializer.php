<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Serialization\Groups;
use App\Domain\Shared\Pagination\Slice;
use JsonSerializable;
use ReflectionObject;
use function array_map;
use function in_array;
use function is_array;
use function is_object;

final class ApiDtoSerializer
{
    public function serialize(mixed $value, string $group): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->serialize($item, $group), $value);
        }

        if (is_object($value)) {
            return $this->serializeObject($value, $group);
        }

        return $value;
    }

    private function serializeObject(object $value, string $group): mixed
    {
        if ($value instanceof Slice) {
            return $this->serialize($value->items(), $group);
        }

        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        $reflection = new ReflectionObject($value);
        $result = [];

        foreach ($reflection->getProperties() as $property) {
            if (!$property->isPublic()) {
                continue;
            }

            $groups = $property->getAttributes(Groups::class);
            if ($groups !== [] && !in_array($group, $groups[0]->newInstance()->groups, true)) {
                continue;
            }

            $result[$property->getName()] = $this->serialize($property->getValue($value), $group);
        }

        if ($result !== []) {
            return $result;
        }

        return $value;
    }
}
