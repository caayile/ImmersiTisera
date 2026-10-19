<?php

namespace App\Mail;

use App\Support\SmtpCertificateBundle;
use Illuminate\Mail\MailManager;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class SmtpCaMailManager extends MailManager
{
    /**
     * @param  array<string, mixed>  $config
     */
    protected function configureSmtpTransport(EsmtpTransport $transport, array $config): EsmtpTransport
    {
        $transport = parent::configureSmtpTransport($transport, $config);

        SmtpCertificateBundle::apply($transport);

        return $transport;
    }
}
