<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use function MongoDB\BSON\toRelaxedExtendedJSON;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect ke provider masing masing untuk mendapat autentikasi
     */
    public function redirectToProvider($provider)
    { // $provider = /login/{provider}/
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Memperoleh informasi User dari provider
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();
        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);

        return redirect($this->redirectTo);
    }

    /**
     * Jika user telah terdaftar maka return user
     * Selain itu, user akan di daftarkan
     */
    public function findOrCreateUser($user, $provider)
    {
        $authUser = User::where('provider_id', $user->id)->first();

        if ($authUser) {
            return $authUser;
        } else {
            return User::create([
                'name' => !empty($user->name) ? $user->name : $user->email, // jika username belum ada, gunakan email sebagai username
                'email' => $user->email,
                'provider' => $provider,
                'provider_id' => $user->id,
                'password' => !empty($user->password) ? $user->password : '', // jika password tidak ada isinya, maka skip
            ]);
        }
    }
}
