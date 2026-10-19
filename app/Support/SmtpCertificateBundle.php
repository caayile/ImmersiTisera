<?php

namespace App\Support;

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class SmtpCertificateBundle
{
    public static function path(): ?string
    {
        $configured = config('mail.mailers.smtp.cafile');

        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        $bundled = resource_path('certs/cacert.pem');

        return is_file($bundled) ? $bundled : null;
    }

    public static function apply(EsmtpTransport $transport): void
    {
        $cafile = self::path();

        if ($cafile === null) {
            return;
        }

        $stream = $transport->getStream();

        if (! $stream instanceof SocketStream) {
            return;
        }

        $options = $stream->getStreamOptions();
        $options['ssl']['cafile'] = $cafile;
        $options['ssl']['verify_peer'] = true;
        $options['ssl']['verify_peer_name'] = true;

        $stream->setStreamOptions($options);
    }
}
