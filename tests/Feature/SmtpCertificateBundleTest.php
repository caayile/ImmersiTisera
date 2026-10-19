<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tests\TestCase;

class SmtpCertificateBundleTest extends TestCase
{
    public function test_smtp_transport_uses_the_bundled_certificate(): void
    {
        config(['mail.mailers.smtp.cafile' => null]);
        Mail::purge('smtp');

        $transport = Mail::mailer('smtp')->getSymfonyTransport();

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertSame(resource_path('certs/cacert.pem'), $transport->getStream()->getStreamOptions()['ssl']['cafile']);
        $this->assertTrue($transport->getStream()->getStreamOptions()['ssl']['verify_peer']);
        $this->assertTrue($transport->getStream()->getStreamOptions()['ssl']['verify_peer_name']);
    }

    public function test_smtp_transport_uses_a_configured_certificate_file(): void
    {
        $configured = resource_path('certs/cacert.pem');
        config(['mail.mailers.smtp.cafile' => $configured]);
        Mail::purge('smtp');

        $transport = Mail::mailer('smtp')->getSymfonyTransport();

        $this->assertInstanceOf(EsmtpTransport::class, $transport);
        $this->assertSame($configured, $transport->getStream()->getStreamOptions()['ssl']['cafile']);
    }
}
