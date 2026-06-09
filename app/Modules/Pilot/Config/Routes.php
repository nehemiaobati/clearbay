<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('pilot', ['namespace' => 'App\Modules\Pilot\Controllers'], static function ($routes) {
    $routes->get('/', 'PilotController::index', ['as' => 'pilot.index']);
    $routes->post('signup', 'PilotController::signup', ['as' => 'pilot.signup', 'filter' => 'throttle:5,60']);
});
