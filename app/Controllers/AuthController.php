<?php
namespace App\Controllers;

use App\Core\Container;
use App\Models\Services\Interfaces\IUserService;
use App\ViewModels\LoginViewModel;
use App\ViewModels\RegisterViewModel;
use App\Core\Exceptions\BusinessException;
use App\Core\Session;

class AuthController extends BaseController
{
    private IUserService $userService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->userService = $container->make(IUserService::class);
    }

    // GET /login
    public function loginForm(): void
    {
        if ($this->getCurrentUserId()) $this->redirect('/');
        $vm = new LoginViewModel();
        $this->render('auth.login', ['vm' => $vm]);
    }

    // POST /login
    public function login(): void
    {
        $this->validateCsrf();

        $vm           = new LoginViewModel();
        $vm->email    = trim($_POST['email']    ?? '');
        $vm->password = trim($_POST['password'] ?? '');

        if (!$vm->validate()) {
            $this->render('auth.login', ['vm' => $vm]);
            return;
        }

        try {
            $user = $this->userService->authenticate($vm->email, $vm->password);
            Session::set('user_id',   $user->id);
            Session::set('user_role', $user->role);
            Session::regenerate();

            $redirect = $_GET['redirect'] ?? '/';
            $this->redirect($redirect);

        } catch (BusinessException $e) {
            $vm->errors['general'] = $e->getMessage();
            $this->render('auth.login', ['vm' => $vm]);
        }
    }

    // GET /register
    public function registerForm(): void
    {
        if ($this->getCurrentUserId()) $this->redirect('/');
        $vm = new RegisterViewModel();
        $this->render('auth.register', ['vm' => $vm]);
    }

    // POST /register
    public function register(): void
    {
        $this->validateCsrf();

        $vm = new RegisterViewModel();
        $vm->username        = trim($_POST['username'] ?? '');
        $vm->email           = trim($_POST['email'] ?? '');
        $vm->password        = trim($_POST['password'] ?? '');
        $vm->confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (!$vm->validate()) {
            $this->render('auth.register', ['vm' => $vm]);
            return;
        }

        try {
            $user = $this->userService->register($vm->username, $vm->email, $vm->password);
            Session::set('user_id',   $user->id);
            Session::set('user_role', $user->role);
            Session::regenerate();

            $this->redirect('/');

        } catch (BusinessException $e) {
            $vm->errors['general'] = $e->getMessage();
            $this->render('auth.register', ['vm' => $vm]);
        }
    }

    // POST /logout
    public function logout(): void
    {
        $this->validateCsrf();
        Session::destroy();
        $this->redirect('/login');
    }
}
