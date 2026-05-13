<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CustomLoginRequest;
use App\Http\Responses\LoginResponse as CustomLoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginRequest::class, CustomLoginRequest::class);
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
    }

    public function boot(): void
    {
        Fortify::authenticateUsing(function (Request $request) {

            if ($request->input('login_type') === 'admin') {
                if (Auth::guard('admin')->attempt([
                    'email' => $request->email,
                    'password' => $request->password,
                ])) {
                    return Auth::guard('admin')->user();
                }
                return null;
            }

            if (Auth::guard('web')->attempt([
                'email' => $request->email,
                'password' => $request->password,
            ])) {
                return Auth::guard('web')->user();
            }

            return null;
        });

        Fortify::authenticateThrough(function (Request $request) {
            return [
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ];
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(fn() => view('auth.login'));
        Fortify::registerView(fn() => view('auth.register'));

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::lower($request->email) . '|' . $request->ip());
        });

        // ✅ メール認証画面の表示定義
        $this->app->singleton(VerifyEmailViewResponse::class, function () {
            return new class implements VerifyEmailViewResponse {
                public function toResponse($request)
                {
                    return view('auth.verify-email');
                }
            };
        });
    }
}
