<?php

namespace App\Models\Group;

class CupGroup
{
    public string $id;
    public string $name;

    public function __construct(
        public GroupMale $male,
        public ?GroupAge $age = null
    ) {
        $this->id = "{$male->value}_".($age ? $age->value : 0);
        $this->name = (($male === GroupMale::Man) ? 'М' : 'Ж').($age ? $age->value : '');
    }

    public function next(): self
    {
        return new self($this->male, $this->age->next());
    }

    public function prev(): self
    {
        return new self($this->male, $this->age->prev());
    }
}
