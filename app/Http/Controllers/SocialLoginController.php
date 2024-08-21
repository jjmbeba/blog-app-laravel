<?php

namespace App\Http\Controllers;

use App\Models\SocialLogin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function toProvider($driver)
    {
        return Socialite::driver($driver)->redirect();
    }

    public function handleCallback($driver)
    {
        $user = Socialite::driver($driver)->user();

        $user_account = SocialLogin::where('provider', $driver)->where('provider_id', $user->getId())->first();

        if ($user_account) {
            auth()->login($user_account->user);
            Session::regenerate();
            return redirect()->route('dashboard');
        }

        $db_user = User::where('email', $user->getEmail())->first();

        if ($db_user) {
            SocialLogin::create([
                'provider' => $driver,
                'provider_id' => $user->getId(),
                'user_id' => $db_user->id
            ]);
        }else{
            $db_user = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar(),
                'password' => bcrypt(rand(100000, 999999))
            ]);

            SocialLogin::create([
                'provider' => $driver,
                'provider_id' => $user->getId(),
                'user_id' => $db_user->id
            ]);
        }

        auth()->login($db_user);
        Session::regenerate();
        return redirect()->route('dashboard');
    }
}
