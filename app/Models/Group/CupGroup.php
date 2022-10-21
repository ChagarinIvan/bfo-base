<?php

namespace App\Models\Group;

class CupGroup
{
    public function __construct(
        private readonly GroupMale $male,
        private readonly ?GroupAge $age = null
    ) {}

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
        return "{$this->male->value}_".($this->age->value ?? 0);
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

    public static function create(GroupMale $male, GroupAge $age): self
    {
        return new self($male, $age);
    }

}
