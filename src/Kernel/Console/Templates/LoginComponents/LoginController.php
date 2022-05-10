<?php

namespace Solital\Components\Controller\Auth;

use Solital\Core\Auth\Auth;
use Solital\Core\Http\Request;
use Solital\Core\Http\Controller\Controller;

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
        Auth::isLogged();

        return view('auth.auth-form', [
            'title' => 'Login',
            'msg' => $this->message->get('login')
        ]);
    }

    /**
     * @return void
     */
    public function authPost(): void
    {
        if (Request::limit('email.login', 3)) {
            $this->message->new('login', 'You have already made 3 login attempts! Please wait 60 seconds and try again.');
            response()->redirect(url('auth'));
        }

        $res = Auth::login('auth_users')
            ->columns('username', 'password')
            ->values('inputEmail', 'inputPassword')
            ->register();

        if ($res == false) {
            $this->message->new('login', 'Invalid username and/or password!');
            response()->redirect(url('auth'));
        }
    }

    /**
     * @return mixed
     */
    public function dashboard(): mixed
    {
        Auth::isNotLogged();

        return view('auth.auth-dashboard', [
            'title' => 'Dashboard',
        ]);
    }

    /**
     * @return void
     */
    public function exit(): void
    {
        $this->message->new('login', 'Logoff successfully!');
        Auth::logoff();
    }
}
