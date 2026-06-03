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
    $routes->get('analytics', 'HospitalController::analytics', ['as' => 'hospital.analytics']);
    $routes->get('analytics/export', 'HospitalController::exportPdf', ['as' => 'hospital.analytics.export']);
});
