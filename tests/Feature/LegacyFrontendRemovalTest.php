<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LegacyFrontendRemovalTest extends TestCase
{
    #[Test]
    public function registration_and_password_email_templates_remain_renderable(): void
    {
        $registration = $this->app['view']->make('emails.registration', ['url' => 'https://example.test/activate'])->render();
        $password = $this->app['view']->make('emails.password', ['password' => 'secret'])->render();

        $this->assertStringContainsString('https://example.test/activate', (string) $registration);
        $this->assertStringContainsString('secret', (string) $password);
    }
}
