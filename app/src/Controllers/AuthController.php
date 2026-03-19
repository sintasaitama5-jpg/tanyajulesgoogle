<?php
namespace App\Controllers;

use App\Auth;
use App\Lang;

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) redirect('/');
        $error = flash('login_error');
        render('auth/login', compact('error'));
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (Auth::login($email, $password)) {
            redirect('/');
        } else {
            flash('login_error', __t('login_failed'));
            redirect('/login');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/login');
    }

    public function setLang(): void
    {
        $lang = $_GET['lang'] ?? 'id';
        $_SESSION['lang'] = in_array($lang, ['id', 'en']) ? $lang : 'id';
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($ref);
    }
}
