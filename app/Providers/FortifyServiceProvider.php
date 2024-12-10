<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request)
            {
                if (Auth::user() && Auth::user()->isAdmin) {
                    return redirect('/admin');
                }

                return redirect('/');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Menangkap event login Fortify
        Event::listen(Login::class, function ($event) {
            // Ambil user yang sedang login
            $user = $event->user;
            $currentIp = request()->ip();
            if ($user) {
                // Cek apakah IP yang digunakan user sudah pernah digunakan oleh user lain
                $existingUser = User::where('last_ip', $currentIp)->first();

                // Jika ada user lain dengan IP yang sama, blokir login
                // if ($user->group === 'user') {
                //     if ($existingUser && $existingUser->id !== $user->id) {
                //         Auth::logout();
                //         abort(403, _('Perangkat ini telah di pakai login oleh user lain. Mohon gunakan perangkat lain nya.'));
                //     }
                // }

                // Jika IP tidak ada masalah, simpan IP untuk user yang baru login
                $user->last_ip = $user->group !== 'user' ? null : $currentIp;
                $user->save();

                return $user;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
