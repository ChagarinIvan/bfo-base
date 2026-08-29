<?php

declare(strict_types=1);

namespace App\Application\Service\Auth;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserDto;
use App\Domain\Auth\UserRepository;
use function array_map;

final readonly class ListUsersService
{
    public function __construct(
        private UserRepository $users,
        private AuthAssembler $assembler,
    )
    {
    }

    /** @return UserDto[] */
    public function execute(): array
    {
        return array_map(
            $this->assembler->toUserDto(...),
            $this->users->all(),
        );
    }
}
