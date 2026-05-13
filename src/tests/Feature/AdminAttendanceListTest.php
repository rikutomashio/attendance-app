<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者作成
     */
    private function createAdmin()
    {
        return User::factory()->create();
    }

    /**
     * ① 今日の全ユーザー勤怠が確認できる
     */
    public function test_管理者は今日の全ユーザー勤怠を確認できる()
    {
        Carbon::setTestNow('2024-01-10');

        $admin = $this->createAdmin();

        $userA = User::factory()->create(['name' => 'Aさん']);
        $userB = User::factory()->create(['name' => 'Bさん']);

        // 今日の勤怠（Aのみ）
        Attendance::factory()->create([
            'user_id' => $userA->id,
            'work_date' => '2024-01-10',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list');

        $response->assertStatus(200)
            ->assertSee('2024-01-10')
            ->assertSee('Aさん')
            ->assertSee('Bさん'); // 勤怠なくても表示される仕様
    }

    /**
     * ② 初期表示で今日の日付が表示される
     */
    public function test_初期表示で今日の日付が表示される()
    {
        Carbon::setTestNow('2024-01-10');

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list');

        $response->assertStatus(200)
            ->assertSee('2024-01-10');
    }

    /**
     * ③ 前日ボタンで前日の勤怠が表示される
     */
    public function test_前日ボタンで前日の勤怠が表示される()
    {
        Carbon::setTestNow('2024-01-10');

        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => 'Aさん']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-09',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=2024-01-09');

        $response->assertStatus(200)
            ->assertSee('2024-01-09')
            ->assertSee('Aさん');
    }

    /**
     * ④ 翌日ボタンで翌日の勤怠が表示される
     */
    public function test_翌日ボタンで翌日の勤怠が表示される()
    {
        Carbon::setTestNow('2024-01-10');

        $admin = $this->createAdmin();
        $user = User::factory()->create(['name' => 'Aさん']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-11',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=2024-01-11');

        $response->assertStatus(200)
            ->assertSee('2024-01-11')
            ->assertSee('Aさん');
    }

    /**
     * ⑤ 未ログインはログイン画面へリダイレクト
     */
    public function test_未ログインはログイン画面にリダイレクトされる()
    {
        $response = $this->get('/admin/attendance/list');

        $response->assertRedirect('/admin/login');
    }

    /**
     * ⑥ 一般ユーザーはアクセスできない
     */
    public function test_一般ユーザーは管理画面にアクセスできない()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user) // webガード
            ->get('/admin/attendance/list');

        $response->assertRedirect('/admin/login');
    }
}
