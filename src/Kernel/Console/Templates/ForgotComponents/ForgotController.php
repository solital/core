<?php

namespace Solital\Components\Controller\Auth;

use Solital\Core\Auth\Auth;
use Solital\Core\Http\Request;
use Solital\Core\Security\Hash;
use Solital\Core\Http\Controller\Controller;

class ForgotController extends Controller
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
    public function forgot(): mixed
    {
        return view('auth.forgot-form', [
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

        if (Request::repeat('email.forgot', $email)) {
            $this->message->new('forgot', 'You have tried this email before!');
            response()->redirect(url('forgot'));
        }

        $res = Auth::forgot('auth_users')
            ->columns('username')
            ->values($email, url('change'))
            ->mailSender('mail_sender@email.com')
            ->register();

        if ($res == true) {
            $this->message->new('forgot', 'Link sent to your email!');
            response()->redirect(url('forgot'));
        }
    }

    /**
     * @param string $hash
     * 
     * @return mixed
     */
    public function change($hash): mixed
    {
        $res = Hash::decrypt($hash)->isValid();

        if ($res == true) {
            $email = Hash::decrypt($hash)->value();

            return view('auth.change-pass-form', [
                'title' => 'Change Password',
                'email' => $email,
                'hash' => $hash,
                'msg' => $this->message->get('forgot')
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
                Auth::change('auth_users')
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
