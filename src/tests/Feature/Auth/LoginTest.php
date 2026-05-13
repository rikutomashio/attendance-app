<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレス未入力バリデーション
     */
    public function test_email_is_required()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);

        $this->assertStringContainsString(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    /**
     * パスワード未入力バリデーション
     */
    public function test_password_is_required()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);

        $this->assertStringContainsString(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    /**
     * 認証情報不一致
     */
    public function test_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'correct@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();

        $this->assertStringContainsString(
            'ログイン情報が登録されていません',
            session('errors')->first('email')
        );
    }

    /**
     * 正常ログイン
     */
    public function test_login_success_redirects_to_attendance()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/attendance');
    }

    /**
     * ログイン後に/loginへアクセスするとリダイレクト
     */
    public function test_authenticated_user_redirects_from_login()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/login');

        $response->assertRedirect('/attendance');
    }

    /**
     * ログアウト後はアクセス制限される
     */
    public function test_logout_blocks_access_to_attendance()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->post('/logout');

        $response = $this->get('/attendance');

        $response->assertRedirect('/login');
    }
}
