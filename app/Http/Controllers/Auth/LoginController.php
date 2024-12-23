<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\LoginLog;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

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
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            LoginLog::create([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role(),
                'ip' => $request->ip(),
                'type' => 'login',
            ]);

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request) {
        if (Auth::user()) {
            LoginLog::create([
                'user_id' => Auth::id(),
                'role_id' => Auth::user()->role(),
                'ip' => $request->ip(),
                'type' => 'logout',
            ]);
        }

        Auth::logout();

        if (!empty(Input::get('del')) && Input::get('del') == 1) {
            Session::flash('message', 'Your account has been deleted!');
            Session::flash('class', 'danger');
            Session::flash('display', 'block');
        }

        return redirect('/login');
    }

    public function username()
    {
        return 'username';
    }
}
