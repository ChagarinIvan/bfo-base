<?php

namespace App\Models\Group;

class CupGroup
{
    public function __construct(
        private readonly GroupMale $male,
        private readonly ?GroupAge $age = null,
        private readonly ?string $name = null,
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
        return "{$this->male->value}_".($this->age->value ?? 0).'_'.$this->name ?: '';
    }

    public function name(): string
    {
        return $this->name ?: ($this->male === GroupMale::Man ? 'М' : 'Ж').($this->age ? $this->age->toString() : '');
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
