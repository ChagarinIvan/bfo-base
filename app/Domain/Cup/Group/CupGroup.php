<?php

declare(strict_types=1);

namespace App\Domain\Cup\Group;

final readonly class CupGroup
{
    public static function create(GroupMale $male, GroupAge $age): self
    {
        return new self($male, $age);
    }

    public function __construct(
        private GroupMale $male,
        private ?GroupAge $age = null,
        private ?string $name = null,
    ) {
    }

    public function male(): GroupMale
    {
        return $this->male;
    }

    public function age(): ?GroupAge
    {
        return $this->age;
    }

    public function id(): string
    {
        return "{$this->male->value}_" . ($this->age?->value ?: 0) . '_' . ($this->name ?: '');
    }

    public function name(): string
    {
        return $this->name ?: ($this->male === GroupMale::Man ? 'М' : 'Ж') . ($this->age ? $this->age->toString() : '');
    }

    public function next(): self
    {
        return new self($this->male, $this->age->next());
    }

    public function prev(): self
    {
        return new self($this->male, $this->age->prev());
    }

    public function equal(self $other): bool
    {
        return $this->id() === $other->id();
    }

    public function older(self $other): bool
    {
        return $this->age()->value > $other->age()->value;
    }

    public function less(self $other): bool
    {
        return $this->age()->value < $other->age()->value;
    }
}
