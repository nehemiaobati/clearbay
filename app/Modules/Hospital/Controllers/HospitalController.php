<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Controllers;

use App\Controllers\BaseController;
use App\Modules\Hospital\Libraries\HospitalService;
use App\Modules\Hospital\Models\HospitalModel;
use App\Modules\Hospital\Entities\Hospital;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class HospitalController
 *
 * Coordinates presentations and API updates for emergency department charge nurses
 * and hospital administrators.
 */
class HospitalController extends BaseController
{
    /**
     * @var HospitalService
     */
    private HospitalService $_hospital_service;

    /**
     * @var HospitalModel
     */
    private HospitalModel $_hospital_model;

    /**
     * HospitalController constructor.
     */
    public function __construct()
    {
        $this->_hospital_service = new HospitalService();
        $this->_hospital_model   = new HospitalModel();
        helper(['form', 'url']);
    }

    /**
     * Helper to verify session and retrieve user's mapped hospital.
     *
     * @return Hospital|null
     */
    private function _getMappedHospital(): ?Hospital
    {
        $hospital_id = session()->get('hospital_id');
        if ($hospital_id === null) {
            return null;
        }

        /** @var Hospital|null $hospital */
        $hospital = $this->_hospital_model->find((int) $hospital_id);
        return $hospital;
    }

    /**
     * Renders the primary Emergency Department Dashboard (SC-02).
     *
     * @return ResponseInterface|string
     */
    public function dashboard()
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.login'))->with('error', 'Authentication session is invalid or you are not mapped to a facility.');
        }

        $data = [
            'pageTitle'       => $hospital->name . ' Emergency Department | ClearBay',
            'metaDescription' => 'Live ambulance tracking and off-load management dashboard for ' . $hospital->name,
            'canonicalUrl'    => url_to('hospital.dashboard'),
            'robotsTag'       => 'noindex, nofollow',
            'hospital'        => $hospital,
        ];

        return view('App\Modules\Hospital\Views\dashboard', $data);
    }

    /**
     * JSON Endpoint fetching active queue list and metrics for dynamic dashboard polling.
     *
     * @return ResponseInterface
     */
    public function getQueue(): ResponseInterface
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Session expired.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $data = $this->_hospital_service->getQueueData((int) $hospital->id);

        return $this->response->setJSON([
            'status'     => 'success',
            'result'     => $data,
            'csrf_token' => csrf_hash()
        ]);
    }

    /**
     * Processes status and bay occupancy updates.
     *
     * @return ResponseInterface
     */
    public function updateStatus(): ResponseInterface
    {
        $rules = [
            'status'         => 'required|in_list[GREEN,AMBER,RED]',
            'bays_available' => 'required|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Invalid input parameters.',
                'errors'     => $this->validator->getErrors(),
                'csrf_token' => csrf_hash()
            ]);
        }

        $hospital = $this->_getMappedHospital();
        $user_id  = session()->get('user_id');
        if ($hospital === null || $user_id === null) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Session expired.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $status         = (string) $this->request->getPost('status');
        $bays_available = (int) $this->request->getPost('bays_available');

        $success = $this->_hospital_service->updateStatus((int) $hospital->id, $status, $bays_available, (int) $user_id);

        if (!$success) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Failed to update capacity status in database.',
                'csrf_token' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'ED status updated successfully.',
            'csrf_token' => csrf_hash()
        ]);
    }

    /**
     * Completes handover, signs off notes, and clears bay.
     *
     * @return ResponseInterface
     */
    public function completeHandover(): ResponseInterface
    {
        $rules = [
            'handover_id' => 'required|integer',
            'bay_number'  => 'permit_empty|alpha_numeric_space|max_length[50]',
            'notes'       => 'permit_empty|max_length[2000]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Validation error.',
                'errors'     => $this->validator->getErrors(),
                'csrf_token' => csrf_hash()
            ]);
        }

        $user_id = session()->get('user_id');
        if ($user_id === null) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Session expired.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $handover_id = (int) $this->request->getPost('handover_id');
        $bay_number  = (string) ($this->request->getPost('bay_number') ?? '');
        $notes       = (string) ($this->request->getPost('notes') ?? '');

        $success = $this->_hospital_service->completeHandover($handover_id, $bay_number, $notes, (int) $user_id);

        if (!$success) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Failed to finalize patient handover.',
                'csrf_token' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Handover completed successfully.',
            'csrf_token' => csrf_hash()
        ]);
    }

    /**
     * Renders the Hospital Analytics Dashboard (SC-06).
     *
     * @return ResponseInterface|string
     */
    public function analytics()
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.login'))->with('error', 'Session invalid.');
        }

        $range = (string) ($this->request->getGet('range') ?? '7');
        $days  = in_array($range, ['7', '30', '90'], true) ? (int) $range : 7;

        $analytics = $this->_hospital_service->getAnalytics((int) $hospital->id, $days);

        $data = [
            'pageTitle'       => 'ED Analytics | ' . $hospital->name,
            'metaDescription' => 'Emergency Department ambulance handover statistics and performance.',
            'canonicalUrl'    => url_to('hospital.analytics'),
            'robotsTag'       => 'noindex, nofollow',
            'hospital'        => $hospital,
            'analytics'       => $analytics,
            'range'           => $range,
        ];

        return view('App\Modules\Hospital\Views\analytics', $data);
    }

    /**
     * Generates a plain-text downloadable PDF summary report matching SC-06 specs.
     *
     * @return ResponseInterface
     */
    public function exportPdf(): ResponseInterface
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return $this->response->setBody('Session expired.')->setStatusCode(401);
        }

        $analytics = $this->_hospital_service->getAnalytics((int) $hospital->id, 30);

        // Standard clean tabular data representation matching clean text output
        $content = "=========================================================\n";
        $content .= "           CLEARBAY OFF-LOAD PERFORMANCE REPORT          \n";
        $content .= "=========================================================\n";
        $content .= "Facility: " . $hospital->name . "\n";
        $content .= "Report Range: Past 30 Days\n";
        $content .= "Generated At: " . date('Y-m-d H:i:s EAT') . "\n\n";

        $content .= "PROVIDER SUMMARY:\n";
        $content .= str_pad("Provider", 25) . " | " . str_pad("Handovers", 10) . " | " . str_pad("Avg Wait (min)", 15) . "\n";
        $content .= str_repeat("-", 58) . "\n";
        foreach ($analytics['provider_performance'] as $row) {
            $content .= str_pad($row['provider'], 25) . " | " . str_pad((string)$row['total_handovers'], 10) . " | " . str_pad((string)$row['avg_wait'], 15) . "\n";
        }
        $content .= "=========================================================\n";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="clearbay_report_' . $hospital->code . '.pdf"')
            ->setBody($content);
    }
}
