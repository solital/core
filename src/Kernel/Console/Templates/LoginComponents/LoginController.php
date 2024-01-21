<?php

namespace Solital\Components\Controller\Auth;

use Solital\Core\Auth\Auth;
use Solital\Core\Http\{Request, Controller\Controller};

class LoginController extends Controller
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function auth(): mixed
    {
        Auth::isRemembering();

        return view('auth.auth-form', [
            'title' => 'Login',
            'msg' => message('login')
        ]);
    }

    /**
     * @return void
     */
    public function authPost(): void
    {
        if (Request::limit('email.login', 3)) {
            message('login', 'You have already made 3 login attempts! Please wait 60 seconds and try again.');
            response()->redirect(url('auth'));
        }

        $res = Auth::login('auth_users')
            ->columns('username', 'password')
            ->values('inputEmail', 'inputPassword')
            ->remember('inputRemember')
            ->register();

        if ($res == false) {
            message('login', 'Invalid username and/or password!');
            response()->redirect(url('auth'));
        }
    }

    /**
     * @return mixed
     */
    public function dashboard(): mixed
    {
        return view('auth.auth-dashboard', [
            'title' => 'Dashboard',
        ]);
    }

    /**
     * @return void
     */
    public function exit(): void
    {
        message('login', 'Logoff successfully!');
        Auth::logoff();
    }
}
