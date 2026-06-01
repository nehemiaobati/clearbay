<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Libraries\AuthService;
use App\Modules\Auth\Entities\User;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class AuthController
 *
 * Coordinates authentication forms, verification, and redirection.
 */
class AuthController extends BaseController
{
    /**
     * @var AuthService
     */
    private AuthService $_auth_service;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->_auth_service = new AuthService();
        helper(['form', 'url']);
    }

    /**
     * Helper to get dashboard redirect path based on user role.
     *
     * @param User $user
     * @return string
     */
    private function _getRedirectRoute(User $user): string
    {
        switch ($user->role) {
            case 'nurse':
            case 'hospital_admin':
                return 'hospital.dashboard';
            case 'paramedic':
                return 'ambulance.home';
            case 'dispatcher':
                return 'dispatcher.index';
            case 'admin':
                return 'admin.dashboard';
            default:
                return 'auth.login';
        }
    }

    /**
     * Renders the login view.
     *
     * @return ResponseInterface|string
     */
    public function loginView(): string|RedirectResponse
    {
        if ($this->_auth_service->isLoggedIn()) {
            $user = $this->_auth_service->getCurrentUser();
            if ($user !== null) {
                return redirect()->to(url_to($this->_getRedirectRoute($user)));
            }
        }

        $data = [
            'page_title'       => 'Sign In | ClearBay',
            'meta_description' => 'Sign in to the ClearBay emergency off-load coordination dashboard.',
            'canonical_url'    => url_to('auth.login'),
            'robots_tag'       => 'noindex, nofollow',
        ];

        return view('App\Modules\Auth\Views\login', $data);
    }

    /**
     * Processes login credentials and initiates session.
     *
     * @return RedirectResponse
     */
    public function login(): RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $user = $this->_auth_service->login($email, $password);

        if ($user === null) {
            return redirect()->back()->withInput()->with('error', 'Incorrect email or password. Please try again.');
        }

        // Check for saved redirect URL from AuthFilter
        $redirect = session()->get('redirect_url');
        if ($redirect) {
            session()->remove('redirect_url');
            return redirect()->to($redirect)->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return redirect()->to(url_to($this->_getRedirectRoute($user)))->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Terminates the user session and logs out.
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        $this->_auth_service->logout();
        return redirect()->to(url_to('auth.login'))->with('success', 'You have been logged out successfully.');
    }
}
