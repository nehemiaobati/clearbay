<?php

declare(strict_types=1);

namespace App\Modules\Ambulance\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('ambulance', ['namespace' => 'App\Modules\Ambulance\Controllers', 'filter' => ['auth', 'role:paramedic']], static function ($routes) {
    $routes->get('/', 'AmbulanceController::home', ['as' => 'ambulance.home']);
    $routes->get('hospital/(:segment)', 'AmbulanceController::detail/$1', ['as' => 'ambulance.hospital.detail']);
    $routes->get('pre-notify/(:num)', 'AmbulanceController::preNotifyForm/$1', ['as' => 'ambulance.pre_notify']);
    $routes->post('pre-notify', 'AmbulanceController::sendPreNotification', ['as' => 'ambulance.pre_notify.submit']);
    $routes->get('run/(:num)', 'AmbulanceController::activeRun/$1', ['as' => 'ambulance.active_run']);
    $routes->post('location', 'AmbulanceController::updateLocation', ['as' => 'ambulance.location.update']);
});
