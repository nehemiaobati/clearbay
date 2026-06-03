<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Verifies the user is authenticated before allowing access.
     *
     * If the user is not logged in, the requested URL is saved to the session
     * for post-login redirect, and the user is redirected to the login page.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('is_logged_in')) {
            // If it's an AJAX request, return JSON with 401 Unauthorized status instead of a redirect
            if (method_exists($request, 'isAJAX') && $request->isAJAX()) {
                /** @var ResponseInterface $response */
                $response = service('response');
                $response->setHeader('Content-Type', 'application/json');
                $response->setBody(json_encode([
                    'status'     => 'error',
                    'message'    => 'Session expired.',
                    'csrf_token' => csrf_hash()
                ]));
                $response->setStatusCode(401);
                return $response;
            }

            // Save the intended URL for post-login redirect
            session()->set('redirect_url', current_url());

            return redirect()->to(url_to('auth.login'));
        }
    }

    /**
     * Allows post-request inspection or modification.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
