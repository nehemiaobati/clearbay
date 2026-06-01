<?php

declare(strict_types=1);

namespace App\Modules\Dispatcher\Controllers;

use App\Controllers\BaseController;
use App\Modules\Dispatcher\Libraries\DispatcherService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class DispatcherController
 *
 * Coordinates presentations and real-time Server-Sent Events telemetry streams
 * for EMS command center dispatchers.
 */
class DispatcherController extends BaseController
{
    /**
     * @var DispatcherService
     */
    private DispatcherService $_dispatcher_service;

    /**
     * DispatcherController constructor.
     */
    public function __construct()
    {
        $this->_dispatcher_service = new DispatcherService();
        helper(['form', 'url']);
    }

    /**
     * Renders the master Dispatcher Map Dashboard (SC-12).
     *
     * @return ResponseInterface|string
     */
    public function index(): string
    {
        $data = [
            'page_title'       => 'Dispatcher Command Centre | ClearBay',
            'meta_description' => 'Live Mapbox fleet tracking and ambulance off-load delay alerts for Nairobi County.',
            'canonical_url'    => url_to('dispatcher.index'),
            'robots_tag'       => 'noindex, nofollow',
            'mapbox_token'    => env('mapboxgl.accessToken'),
        ];

        return view('App\Modules\Dispatcher\Views\map', $data);
    }

    /**
     * JSON API Endpoint returning current active fleet, hospitals, and alerts.
     *
     * @return ResponseInterface
     */
    public function fleetStatus(): ResponseInterface
    {
        $telemetry = $this->_dispatcher_service->getTelemetry();

        return $this->response->setJSON([
            'status'     => 'success',
            'result'     => $telemetry,
            'csrf_token' => csrf_hash()
        ]);
    }

    /**
     * Processes alert acknowledgment commands.
     *
     * @param string $id
     * @return ResponseInterface
     */
    public function acknowledgeAlert(string $id): ResponseInterface
    {
        $user_id = session()->get('user_id');
        if ($user_id === null) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Session expired.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $alert_id = (int) $id;
        $success  = $this->_dispatcher_service->acknowledgeAlert($alert_id, (int) $user_id);

        if (!$success) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Failed to acknowledge alert.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $telemetry = $this->_dispatcher_service->getTelemetry();

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Alert acknowledged successfully.',
            'result'     => $telemetry,
            'csrf_token' => csrf_hash()
        ]);
    }

    /**
     * Telemetry stream transmitting Server-Sent Events (SSE).
     *
     * @return void
     */
    public function sseStream(): void
    {
        // 1. Establish SSE streaming headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable buffering on Nginx for live flush

        // 2. Prevent session locking during the loop
        session_write_close();

        // 3. Send initial token connection packet
        $init_packet = [
            'status'     => 'connected',
            'csrf_token' => csrf_hash()
        ];
        echo "data: " . json_encode($init_packet) . "\n\n";
        ob_flush();
        flush();

        // 4. Run loop streaming updates every 5 seconds
        $loop_count = 0;
        while ($loop_count < 10) { // Limit to 10 cycles to avoid thread exhaust, browser reconnects automatically
            $telemetry = $this->_dispatcher_service->getTelemetry();

            echo "data: " . json_encode([
                'status' => 'update',
                'result' => $telemetry
            ]) . "\n\n";

            ob_flush();
            flush();
            sleep(5);
            $loop_count++;
        }
    }
}
