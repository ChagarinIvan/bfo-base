<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Club;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LegacyClubRoutesTest extends TestCase
{
    #[Test]
    public function legacy_get_routes_redirect_to_spa(): void
    {
        $this->get('/clubs')->assertMovedPermanently()->assertRedirect('/app/clubs');
        $this->get('/clubs/create')->assertMovedPermanently()->assertRedirect('/app/clubs/create');
        $this->get('/clubs/42/show')->assertMovedPermanently()->assertRedirect('/app/clubs/42');
    }

    #[Test]
    public function legacy_store_route_is_removed(): void
    {
        $this->post('/clubs/store', ['name' => 'Club'])->assertNotFound();
    }
}
