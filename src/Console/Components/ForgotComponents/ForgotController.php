<?php

namespace Solital\Components\Controller\Auth;

use Solital\Components\Controller\Controller;
use Solital\Core\Auth\Auth;
use Solital\Core\Wolf\Wolf;
use Solital\Core\Security\Hash;

class ForgotController extends Controller
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        Auth::defineUrl(url('auth'), url('dashboard'));
    }

    /**
     * @return void
     */
    public function forgot(): void
    {
        Wolf::loadView('auth.forgot-form', [
            'title' => 'Forgot Password',
            'msg' => $this->message->get('forgot')
        ]);
    }

    /**
     * @return void
     */
    public function forgotPost(): void
    {
        $email = input()->post('email')->getValue();

        if (request_repeat('email.forgot', $email)) {
            $this->message->new('forgot', 'You have tried this email before!');
            response()->redirect(url('forgot'));
        }

        $res = Auth::forgot('tb_auth')
            ->columns('username')
            ->values($email, url('change'))
            ->register();

        if ($res == true) {
            $this->message->new('forgot', 'Link sent to your email!');
            response()->redirect(url('forgot'));
        }
    }

    /**
     * @param string $hash
     * 
     * @return void
     */
    public function change($hash): void
    {
        $res = Hash::decrypt($hash)->isValid();

        if ($res == true) {
            $email = Hash::decrypt($hash)->value();

            Wolf::loadView('auth.change-pass-form', [
                'title' => 'Change Password',
                'email' => $email,
                'hash' => $hash
            ]);
        } else {
            $this->message->new('login', 'The informed link has already expired!');
            response()->redirect(url('auth'));
        }
    }

    /**
     * @param string $hash
     * 
     * @return void
     */
    public function changePost($hash): void
    {
        $res = Hash::decrypt($hash)->isValid();
        $email = Hash::decrypt($hash)->value();

        if ($res == true) {
            $pass = input()->post('inputPass')->getValue();
            $confPass = input()->post('inputConfPass')->getValue();

            if ($pass != $confPass) {
                $this->message->new('forgot', 'The fields do not match!');
                response()->redirect(url('change', ['hash' => $hash]));
            } else {
                Auth::change('tb_auth')
                    ->columns('username', 'password')
                    ->values($email, $pass)
                    ->register();

                $this->message->new('login', 'Password changed successfully!');
                response()->redirect(url('auth'));
            }
        } else {
            response()->redirect(url('auth'));
        }
    }
}
