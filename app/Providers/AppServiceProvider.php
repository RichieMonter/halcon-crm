<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(fn ($user, string $url) => $this->accountMail()
            ->subject('Verifica tu correo electrónico · Kopion CRM')
            ->line('Confirma tu correo electrónico para activar el acceso a tu cuenta.')
            ->action('Verificar correo electrónico', $url)
            ->line('Si tú no creaste esta cuenta, puedes ignorar este mensaje.'));

        ResetPassword::toMailUsing(fn ($user, string $token) => $this->accountMail()
            ->subject('Restablece tu contraseña · Kopion CRM')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer contraseña', route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]))
            ->line('Este enlace vence en '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutos.')
            ->line('Si tú no solicitaste el cambio, puedes ignorar este mensaje.'));
    }

    private function accountMail(): MailMessage
    {
        return (new MailMessage)->view([
            'html' => 'emails.account',
            'text' => 'emails.account-text',
        ]);
    }
}
