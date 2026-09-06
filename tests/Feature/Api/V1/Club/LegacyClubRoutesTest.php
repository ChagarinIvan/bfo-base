<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Club;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LegacyClubRoutesTest extends TestCase
{
    #[Test]
    public function legacy_get_routes_are_removed(): void
    {
        $this->get('/clubs')->assertNotFound();
        $this->get('/clubs/create')->assertNotFound();
        $this->get('/clubs/42/show')->assertNotFound();
    }

    #[Test]
    public function legacy_store_route_is_removed(): void
    {
        $this->post('/clubs/store', ['name' => 'Club'])->assertNotFound();
    }
}
