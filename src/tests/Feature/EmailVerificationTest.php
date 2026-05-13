<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録後に認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::first();

        Notification::assertSentTo($user, VerifyEmail::class);

        // ★ここ追加（重要）
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertRedirect('/email/verify');
    }

    /**
     * 未認証ユーザーは認証画面へリダイレクトされる
     */
    public function test_unverified_user_is_redirected_to_email_verification()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertRedirect('/email/verify');
    }

    /**
     * 認証URLにアクセスすると認証完了し、勤怠画面へ遷移
     */
    public function test_email_can_be_verified_and_redirected()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $user->refresh();

        // DB更新確認
        $this->assertNotNull($user->email_verified_at);

        // リダイレクト確認（クエリ付き）
        $response->assertRedirect('/attendance?verified=1');
    }

    /**
     * 認証メール再送機能
     */
    public function test_verification_email_can_be_resent()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)->post('/email/verification-notification');

        // 再送されていることを確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
