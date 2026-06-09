<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('hospital', ['namespace' => 'App\Modules\Hospital\Controllers', 'filter' => ['auth', 'role:hospital_admin,nurse']], static function ($routes) {
    $routes->get('dashboard', 'HospitalController::dashboard', ['as' => 'hospital.dashboard']);
    $routes->get('queue', 'HospitalController::getQueue', ['as' => 'hospital.queue']);
    $routes->post('status', 'HospitalController::updateStatus', ['as' => 'hospital.status.update']);
    $routes->post('handover', 'HospitalController::completeHandover', ['as' => 'hospital.handover.complete']);
    $routes->post('handover/arrived', 'HospitalController::markArrived', ['as' => 'hospital.handover.arrived']);
    $routes->get('analytics', 'HospitalController::analytics', ['as' => 'hospital.analytics']);
    $routes->get('analytics/export', 'HospitalController::exportPdf', ['as' => 'hospital.analytics.export']);
});

// Hospital Admin user management (hospital_admin only)
$routes->group('hospital/users', ['namespace' => 'App\Modules\Hospital\Controllers', 'filter' => ['auth', 'role:hospital_admin']], static function ($routes) {
    $routes->get('/', 'HospitalController::usersList', ['as' => 'hospital.users.list']);
    $routes->get('new', 'HospitalController::userNew', ['as' => 'hospital.users.new']);
    $routes->post('create', 'HospitalController::userCreate', ['as' => 'hospital.users.create']);
    $routes->get('edit/(:num)', 'HospitalController::userEdit/$1', ['as' => 'hospital.users.edit']);
    $routes->post('update/(:num)', 'HospitalController::userUpdate/$1', ['as' => 'hospital.users.update']);
    $routes->get('delete/(:num)', 'HospitalController::userDelete/$1', ['as' => 'hospital.users.delete']);
});
