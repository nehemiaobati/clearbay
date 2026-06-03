<?php

declare(strict_types=1);

namespace App\Modules\Dispatcher\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('dispatcher', ['namespace' => 'App\Modules\Dispatcher\Controllers', 'filter' => ['auth', 'role:dispatcher']], static function ($routes) {
    $routes->get('/', 'DispatcherController::index', ['as' => 'dispatcher.index']);
    $routes->get('fleet-status', 'DispatcherController::fleetStatus', ['as' => 'dispatcher.fleet']);
    $routes->post('alerts/(:num)/acknowledge', 'DispatcherController::acknowledgeAlert/$1', ['as' => 'dispatcher.alert.acknowledge']);
    $routes->get('sse-updates', 'DispatcherController::sseStream', ['as' => 'dispatcher.sse']);
});
