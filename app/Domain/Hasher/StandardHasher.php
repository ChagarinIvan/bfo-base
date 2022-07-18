<?php

namespace App\Domain\Hasher;

final class StandardHasher implements Hasher
{
    /**
     * хеш от строки, значений массива, значений полей объекта или приведения к строке другого типа
     */
    public function hash(mixed $value): string
    {
        if (is_string($value)) {
            return hash('sha256', $value);
        } elseif (is_array($value)) {
            $arrayValue = $value;
            sort($arrayValue);
            return $this->hash(json_encode($arrayValue));
        } elseif (is_callable($value)) {
            return $this->hash($value());
        } elseif (is_object($value)) {
            return $this->hash(serialize($value));
        }

        return $this->hash((string) $value);
    }
}
