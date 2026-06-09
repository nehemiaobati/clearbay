<?php

declare(strict_types=1);

namespace App\Modules\Queue\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->group('api', ['namespace' => 'App\Modules\Queue\Controllers'], static function ($routes) {
    $routes->get('queue', 'QueueController::index', ['as' => 'api.queue.get']);
    $routes->post('queue/action', 'QueueController::action', ['as' => 'api.queue.action']);
});
