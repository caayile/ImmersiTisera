<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImersiAlert extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->shouldMail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    private function shouldMail(object $notifiable): bool
    {
        if (! $notifiable instanceof User || ! filled($notifiable->email)) {
            return false;
        }

        if (! $notifiable->isMentor() && ! $notifiable->isParticipant()) {
            return false;
        }

        return $this->mailerIsReady();
    }

    private function mailerIsReady(): bool
    {
        $mailer = (string) config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return true;
        }

        if ($mailer !== 'smtp') {
            return filled(config('mail.from.address'));
        }

        $username = trim((string) config('mail.mailers.smtp.username'));
        $password = trim((string) config('mail.mailers.smtp.password'));

        return $username !== '' && strcasecmp($username, 'null') !== 0
            && $password !== '' && strcasecmp($password, 'null') !== 0;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Halo, '.$notifiable->name)
            ->line($this->message);

        if (filled($this->url)) {
            $mail->action('Buka di Magang Dosen', url($this->url));
        }

        return $mail->salutation('Magang Dosen · TSU Industry Immersion');
    }

    /**
     * @return array{title: string, message: string, url: string|null}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
