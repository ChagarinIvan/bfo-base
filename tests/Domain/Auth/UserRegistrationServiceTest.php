<?php

declare(strict_types=1);

namespace Tests\Domain\Auth;

use App\Domain\Auth\Factory\UserFactory;
use App\Domain\Auth\InvalidEmail;
use App\Domain\Auth\PasswordGenerator;
use App\Domain\Auth\PasswordHasher;
use App\Domain\Auth\User;
use App\Domain\Auth\UserRegistrationService;
use App\Domain\Auth\UserRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserRegistrationServiceTest extends TestCase
{
    #[Test]
    public function it_creates_a_user_with_a_generated_hashed_password(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('__set')->with('password', 'hashed-password');
        $users = $this->createMock(UserRepository::class);
        $users->expects($this->once())->method('byEmail')->with('new@example.com')->willReturn(null);
        $users->expects($this->once())->method('add')->with($user);
        $factory = $this->createMock(UserFactory::class);
        $factory->expects($this->once())->method('create')->with('new@example.com')->willReturn($user);
        $passwords = $this->createMock(PasswordGenerator::class);
        $passwords->expects($this->once())->method('generate')->willReturn('plain-password');
        $hashes = $this->createMock(PasswordHasher::class);
        $hashes->expects($this->once())->method('hash')->with('plain-password')->willReturn('hashed-password');

        $password = new UserRegistrationService($users, $factory, $passwords, $hashes)->register('new@example.com');

        $this->assertSame('plain-password', $password);
    }

    #[Test]
    public function it_rejects_an_invalid_email(): void
    {
        $service = new UserRegistrationService(
            $this->createStub(UserRepository::class),
            $this->createStub(UserFactory::class),
            $this->createStub(PasswordGenerator::class),
            $this->createStub(PasswordHasher::class),
        );

        $this->expectException(InvalidEmail::class);

        $service->register('invalid');
    }
}
