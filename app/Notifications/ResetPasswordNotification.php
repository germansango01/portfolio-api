<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * @OA\Schema(
 *   schema="ResetPasswordNotification",
 *   description="Notificación de reset de contraseña con link al frontend"
 * )
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public $email;

    /**
     * Create a new notification instance.
     */
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = "https://frontend.com/reset-password?token={$this->token}&email={$this->email}";


        return redirect(env('FRONTEND_VERIFICATION_URL') . '?status=already-verified');


        return (new MailMessage())
            ->subject('Restablecer contraseña')
            ->line('Recibiste este correo porque solicitaste restablecer tu contraseña.')
            ->action('Restablecer contraseña', $url)
            ->line('Si no solicitaste este cambio, ignora este correo.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
