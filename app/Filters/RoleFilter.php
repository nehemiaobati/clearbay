<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class RoleFilter
 *
 * Restricts access to route groups based on user roles.
 * Usage in routes: ['filter' => 'role:admin'] or ['filter' => 'role:hospital_admin,nurse']
 */
class RoleFilter implements FilterInterface
{
    /**
     * Verifies the user's session role is authorized before allowing access.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments Comma-separated list of allowed roles (e.g., ['admin'] or ['hospital_admin,nurse'])
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $user_role = session()->get('user_role');

        if ($user_role === null) {
            return redirect()->to(url_to('auth.login'))->with('error', 'Session expired.');
        }

        if ($arguments === null || count($arguments) === 0) {
            return; // No role restrictions specified, allow access
        }

        $allowed_roles = [];
        foreach ($arguments as $arg) {
            $allowed_roles = array_merge($allowed_roles, explode(',', $arg));
        }

        if (!in_array($user_role, $allowed_roles, true)) {
            // AJAX requests get JSON error (header check to avoid interface type conflict)
            if (strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
                /** @var ResponseInterface $response */
                $response = service('response');
                $response->setHeader('Content-Type', 'application/json');
                $response->setBody(json_encode([
                    'status'     => 'error',
                    'message'    => 'You do not have permission to access this resource.',
                    'csrf_token' => csrf_hash(),
                ]));
                $response->setStatusCode(403);
                return $response;
            }

            // Safe fallback redirect to avoid redirect loops if there is no referer or referer is the same page
            $referrer = $request->getHeaderLine('Referer');
            if (empty($referrer) || urldecode(current_url()) === urldecode($referrer)) {
                return redirect()->to(url_to('home'))->with('error', 'You do not have permission to access this resource.');
            }

            return redirect()->back()->with('error', 'You do not have permission to access this resource.');
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
