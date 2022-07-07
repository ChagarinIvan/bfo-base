<?php

namespace App\Domain\Hasher;

final class StandardHasher implements Hasher
{
    public function hash(mixed $value): string
    {
        if (is_string($value)) {
            return hash('sha256', $value);
        } elseif (is_array($value)) {
            return $this->hash(implode('_', $value));
        } elseif (is_object($value)) {
            return $this->hash(get_object_vars($value));
        }

        return $this->hash((string) $value);
    }
}
