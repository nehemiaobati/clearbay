<?php

declare(strict_types=1);

namespace App\Modules\Queue\Controllers;

use App\Controllers\BaseController;
use App\Modules\Queue\Libraries\QueueService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class QueueController
 *
 * REST API Controller for handling ambulance off-load queue and operations dashboard.
 *
 * @package App\Modules\Queue\Controllers
 */
class QueueController extends BaseController
{
    /**
     * @var QueueService
     */
    private QueueService $_queue_service;

    /**
     * QueueController constructor.
     */
    public function __construct()
    {
        $this->_queue_service = new QueueService();
    }

    /**
     * Retrieves the current ambulance queue and today's analytics metrics.
     *
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $data = $this->_queue_service->getQueueData();
        
        return $this->response->setJSON([
            'status'     => 'success',
            'result'     => $data,
            'csrf_token' => csrf_hash(),
        ]);
    }

    /**
     * Processes a dashboard action for a handover unit (Acknowledge, Prepare, Clear).
     *
     * @return ResponseInterface
     */
    public function action(): ResponseInterface
    {
        $rules = [
            'handoverId' => 'required|integer',
            'actionName' => 'required|in_list[acknowledge,prepare,clear]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Invalid operation parameters.',
                'errors'     => $this->validator->getErrors(),
                'csrf_token' => csrf_hash(),
            ]);
        }

        $handover_id = (int) $this->request->getPost('handoverId');
        $action_name = (string) $this->request->getPost('actionName');

        $success = $this->_queue_service->executeAction($handover_id, $action_name);

        if (!$success) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Failed to update handover status or record not found.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        // Return the updated queue data and metrics immediately to keep the client synced.
        $data = $this->_queue_service->getQueueData();

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Operation executed successfully.',
            'result'     => $data,
            'csrf_token' => csrf_hash(),
        ]);
    }
}
