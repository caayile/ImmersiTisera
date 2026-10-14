<?php

namespace Tests\Unit;

use App\Support\NotificationLink;
use PHPUnit\Framework\TestCase;

class NotificationLinkTest extends TestCase
{
    public function test_legacy_certificates_path_rewrites_to_evaluation(): void
    {
        $this->assertSame('/participant/evaluation#sertifikat', NotificationLink::normalize('/participant/certificates'));
    }

    public function test_absolute_localhost_url_is_stripped_to_path(): void
    {
        $this->assertSame('/participant/logbooks', NotificationLink::normalize('http://localhost/participant/logbooks'));
    }

    public function test_href_rewrites_legacy_certificates_url(): void
    {
        $this->assertSame('/participant/evaluation#sertifikat', NotificationLink::href('http://localhost/participant/certificates'));
    }

    public function test_normalize_strips_bare_localhost_prefix(): void
    {
        $this->assertSame('/participant/evaluation#sertifikat', NotificationLink::normalize('localhost/participant/certificates'));
    }

    public function test_evaluation_fragment_is_preserved(): void
    {
        $this->assertSame(
            '/participant/evaluation#sertifikat',
            NotificationLink::normalize('/participant/evaluation#sertifikat')
        );
    }
}
