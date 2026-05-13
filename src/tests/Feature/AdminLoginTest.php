<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレス未入力
     */
    public function test_email_is_required_for_admin_login()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
            'login_type' => 'admin',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        $this->assertGuest('admin');
    }

    /**
     * パスワード未入力
     */
    public function test_password_is_required_for_admin_login()
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => '',
            'login_type' => 'admin',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        $this->assertGuest('admin');
    }

    /**
     * 認証失敗（メール or パスワード不一致）
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
            'login_type' => 'admin',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest('admin');
    }
}
