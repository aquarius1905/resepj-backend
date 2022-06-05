<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\Administrator;
use App\Models\ShopRepresentative;
use App\Models\User;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //会員登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = null;
            if($request->is('admins/*')) {
                $user = Administrator::where('email', $request->email)->first();
            } else if($request->is('shops/*')) {
                $user = ShopRepresentative::where('email', $request->email)->first();
            } else {
                $user = User::where('email', $request->email)->first();
            }
            if ($user &&
                Hash::check($request->password, $user->password)) {
                return $user;
            } else {
                throw ValidationException::withMessages([
                    'login_error' => "メールアドレス・パスワードが一致しません"
                ]);
            }
        });
    }
}
