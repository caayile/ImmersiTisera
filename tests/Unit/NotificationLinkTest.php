<?php

namespace Tests\Unit;

use App\Support\NotificationLink;
use PHPUnit\Framework\TestCase;

class NotificationLinkTest extends TestCase
{
    public function test_certificate_path_is_preserved(): void
    {
        $this->assertSame('/participant/certificates', NotificationLink::normalize('/participant/certificates'));
    }

    public function test_absolute_localhost_url_is_stripped_to_path(): void
    {
        $this->assertSame('/participant/logbooks', NotificationLink::normalize('http://localhost/participant/logbooks'));
    }

    public function test_href_uses_relative_path_without_http_request(): void
    {
        $this->assertSame('/participant/certificates', NotificationLink::href('http://localhost/participant/certificates'));
    }

    public function test_normalize_strips_bare_localhost_prefix(): void
    {
        $this->assertSame('/participant/certificates', NotificationLink::normalize('localhost/participant/certificates'));
    }
}
