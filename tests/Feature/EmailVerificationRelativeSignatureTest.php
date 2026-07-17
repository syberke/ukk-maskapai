<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationRelativeSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_link_remains_valid_when_local_hostname_changes(): void
    {
        config(['app.url' => 'http://localhost:8000']);

        $user = User::factory()->unverified()->create();
        $mail = (new VerifyEmailNotification)->toMail($user);
        $parts = parse_url($mail->actionUrl);

        $verificationUrl = 'http://127.0.0.1:8000'.$parts['path'].'?'.$parts['query'];

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
