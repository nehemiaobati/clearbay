<?php

declare(strict_types=1);

namespace App\Modules\Auth\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('/', ['namespace' => 'App\Modules\Auth\Controllers'], static function ($routes) {
    $routes->get('login', 'AuthController::loginView', ['as' => 'auth.login']);
    $routes->post('login', 'AuthController::login', ['as' => 'auth.login.submit']);
    $routes->get('logout', 'AuthController::logout', ['as' => 'auth.logout']);
});
