<?php

declare(strict_types=1);

namespace Tests\Feature\Cup;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CupEventViewRetirementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_legacy_cup_event_group_show_route_is_not_registered(): void
    {
        $this->get('/cups/1/2/M21/show')->assertNotFound();
    }
}
