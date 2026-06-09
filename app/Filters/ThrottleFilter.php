<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Class ThrottleFilter
 *
 * Implements rate limiting on specific endpoints using the native Throttler service.
 *
 * @package App\Filters
 * @author Senior Developer
 * @since 1.0.0
 */
class ThrottleFilter implements FilterInterface
{
    /**
     * Check rate limit before executing the request.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     * @return ResponseInterface|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        // Rate limit: 60 requests per minute per IP address
        $key = 'throttle_' . md5($request->getIPAddress());

        if ($throttler->check($key, 60, MINUTE, 1) === false) {
            return Services::response()
                ->setStatusCode(429)
                ->setBody('Too Many Requests');
        }

        return null;
    }

    /**
     * No action required after execution.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-action required
    }
}
