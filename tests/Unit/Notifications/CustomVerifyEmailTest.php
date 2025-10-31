<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CustomVerifyEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_notification_is_sent_and_has_the_correct_content(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->notify(new CustomVerifyEmail());

        Notification::assertSentTo(
            $user,
            CustomVerifyEmail::class,
            function ($notification, $channels) use ($user) {
                $mailData = $notification->toMail($user)->toArray();

                $this->assertEquals('Confirma tu correo electrónico', $mailData['subject']);
                $this->assertEquals('¡Hola ' . $user->name . '!', $mailData['greeting']);
                $this->assertStringContainsString('Gracias por registrarte.', $mailData['introLines'][0]);
                $this->assertEquals('Verificar correo', $mailData['actionText']);
                $this->assertStringContainsString('Si no creaste esta cuenta, puedes ignorar este mensaje.', $mailData['outroLines'][0]);

                return true;
            }
        );
    }
}
