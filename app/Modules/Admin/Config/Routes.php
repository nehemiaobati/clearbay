<?php

declare(strict_types=1);

namespace App\Modules\Admin\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('admin', ['namespace' => 'App\Modules\Admin\Controllers'], static function ($routes) {
    $routes->get('/', 'AdminController::dashboard', ['as' => 'admin.dashboard']);

    // Pilots CRUD
    $routes->group('pilots', static function ($routes) {
        $routes->get('/', 'AdminController::pilotsList', ['as' => 'admin.pilots.list']);
        $routes->get('new', 'AdminController::pilotNew', ['as' => 'admin.pilots.new']);
        $routes->post('create', 'AdminController::pilotCreate', ['as' => 'admin.pilots.create']);
        $routes->get('edit/(:num)', 'AdminController::pilotEdit/$1', ['as' => 'admin.pilots.edit']);
        $routes->post('update/(:num)', 'AdminController::pilotUpdate/$1', ['as' => 'admin.pilots.update']);
        $routes->get('delete/(:num)', 'AdminController::pilotDelete/$1', ['as' => 'admin.pilots.delete']);
    });

    // Handovers CRUD
    $routes->group('handovers', static function ($routes) {
        $routes->get('/', 'AdminController::handoversList', ['as' => 'admin.handovers.list']);
        $routes->get('new', 'AdminController::handoverNew', ['as' => 'admin.handovers.new']);
        $routes->post('create', 'AdminController::handoverCreate', ['as' => 'admin.handovers.create']);
        $routes->get('edit/(:num)', 'AdminController::handoverEdit/$1', ['as' => 'admin.handovers.edit']);
        $routes->post('update/(:num)', 'AdminController::handoverUpdate/$1', ['as' => 'admin.handovers.update']);
        $routes->get('delete/(:num)', 'AdminController::handoverDelete/$1', ['as' => 'admin.handovers.delete']);
    });

    // Hospitals CRUD
    $routes->group('hospitals', static function ($routes) {
        $routes->get('/', 'AdminController::hospitalsList', ['as' => 'admin.hospitals.list']);
        $routes->get('new', 'AdminController::hospitalNew', ['as' => 'admin.hospitals.new']);
        $routes->post('create', 'AdminController::hospitalCreate', ['as' => 'admin.hospitals.create']);
        $routes->get('edit/(:num)', 'AdminController::hospitalEdit/$1', ['as' => 'admin.hospitals.edit']);
        $routes->post('update/(:num)', 'AdminController::hospitalUpdate/$1', ['as' => 'admin.hospitals.update']);
        $routes->get('delete/(:num)', 'AdminController::hospitalDelete/$1', ['as' => 'admin.hospitals.delete']);
    });

    // Ambulances CRUD
    $routes->group('ambulances', static function ($routes) {
        $routes->get('/', 'AdminController::ambulancesList', ['as' => 'admin.ambulances.list']);
        $routes->get('new', 'AdminController::ambulanceNew', ['as' => 'admin.ambulances.new']);
        $routes->post('create', 'AdminController::ambulanceCreate', ['as' => 'admin.ambulances.create']);
        $routes->get('edit/(:num)', 'AdminController::ambulanceEdit/$1', ['as' => 'admin.ambulances.edit']);
        $routes->post('update/(:num)', 'AdminController::ambulanceUpdate/$1', ['as' => 'admin.ambulances.update']);
        $routes->get('delete/(:num)', 'AdminController::ambulanceDelete/$1', ['as' => 'admin.ambulances.delete']);
    });

    // Users CRUD
    $routes->group('users', static function ($routes) {
        $routes->get('/', 'AdminController::usersList', ['as' => 'admin.users.list']);
        $routes->get('new', 'AdminController::userNew', ['as' => 'admin.users.new']);
        $routes->post('create', 'AdminController::userCreate', ['as' => 'admin.users.create']);
        $routes->get('edit/(:num)', 'AdminController::userEdit/$1', ['as' => 'admin.users.edit']);
        $routes->post('update/(:num)', 'AdminController::userUpdate/$1', ['as' => 'admin.users.update']);
        $routes->get('delete/(:num)', 'AdminController::userDelete/$1', ['as' => 'admin.users.delete']);
    });
});
