<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CorsTest extends TestCase
{
    #[Test]
    public function default_origins_are_explicit_and_never_wildcard(): void
    {
        $origins = config('cors.allowed_origins');

        $this->assertIsArray($origins);
        $this->assertContains(config('app.url'), $origins);
        $this->assertContains('http://localhost', $origins);
        $this->assertContains('http://127.0.0.1', $origins);
        $this->assertNotContains('*', $origins);
    }
}
