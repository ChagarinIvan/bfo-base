<?php

namespace Tests\Domain\Hasher;

use App\Domain\Hasher\StandardHasher;
use Tests\TestCase;

class StandardHasherTest extends TestCase
{
    protected function setUp(): void
    {
        $this->hasher = new StandardHasher();
    }

    /** @test */
    public function it_hashes_string(): void
    {
        $hash = $this->hasher->hash('test');
        $this->assertEquals('9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', $hash);

        $newHash = $this->hasher->hash('test');
        $this->assertEquals($hash, $newHash);
    }
}
