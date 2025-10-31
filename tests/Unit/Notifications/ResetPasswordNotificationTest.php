<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResetPasswordNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_notification_is_sent_and_has_the_correct_content(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = Str::random(60);

        $user->notify(new ResetPasswordNotification($token, $user->email));

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function ($notification, $channels) use ($user, $token) {
                $mailData = $notification->toMail($user)->toArray();

                $this->assertEquals('Restablecer contraseña', $mailData['subject']);
                $this->assertStringContainsString('Recibiste este correo porque solicitaste restablecer tu contraseña.', $mailData['introLines'][0]);
                $this->assertEquals('Restablecer contraseña', $mailData['actionText']);
                $this->assertStringContainsString('Si no solicitaste este cambio, ignora este correo.', $mailData['outroLines'][0]);

                $url = env('FRONTEND_AUTH_URL') . "/reset-password?token={$token}&email={$user->email}";
                $this->assertEquals($url, $mailData['actionUrl']);

                return true;
            }
        );
    }
}
