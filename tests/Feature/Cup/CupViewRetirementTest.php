<?php

declare(strict_types=1);

namespace Tests\Feature\Cup;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CupViewRetirementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_legacy_cup_show_route_is_not_registered(): void
    {
        $this->get('/cups/101/show')->assertNotFound();
    }
}
