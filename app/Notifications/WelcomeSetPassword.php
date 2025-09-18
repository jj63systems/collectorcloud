<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WelcomeSetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }


    public function toMail($notifiable): MailMessage
    {
        $tenant = \Spatie\Multitenancy\Models\Tenant::current();
        $domain = $tenant?->domain ?? parse_url(config('app.url'), PHP_URL_HOST);

        // Generate a full URL with https://
        $resetUrl = URL::route('tenant.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], true); // true = absolute URL


        Log::info('Reset URL', [$resetUrl]);
        // Swap the host to tenant’s domain if needed
        $resetUrl = preg_replace('#://[^/]+#', '://'.$domain, $resetUrl);

        return (new MailMessage)
            ->subject('Welcome to CollectorCloud - Set Your Password')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your account has been created.')
            ->line('Please click the button below to set your password:')
            ->action('Set Password', $resetUrl)
            ->line('If you did not expect this email, no further action is required.');

    }
}
