<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function file_get_contents;

final class SpaRoutingTest extends TestCase
{
    #[Test]
    public function nginx_keeps_spa_fallback_before_legacy_php_location(): void
    {
        $config = file_get_contents(base_path('enviroment/nginx/conf.d/app.conf.example'));

        $this->assertIsString($config);
        $this->assertStringContainsString('location ^~ /app/', $config);
        $this->assertStringContainsString('try_files $uri $uri/ /spa/index.html;', $config);
        $this->assertStringContainsString('location ~ \\.php$', $config);
    }

    #[Test]
    public function legacy_groups_route_is_not_replaced_by_spa(): void
    {
        $this->assertNotSame(404, $this->get('/groups')->getStatusCode());
    }
}
