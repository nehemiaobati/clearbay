<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Controllers;

use App\Controllers\BaseController;
use App\Modules\Hospital\Libraries\HospitalService;
use App\Modules\Hospital\Entities\Hospital;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

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
    private HospitalService $hospital_service;

    /**
     * HospitalController constructor.
     */
    public function __construct()
    {
        $this->hospital_service = new HospitalService();
        helper(['form', 'url']);
    }

    // --- Helper Methods ---

    /**
     * Helper to verify session and retrieve user's mapped hospital.
     *
     * @return Hospital|null
     */
    private function _getMappedHospital(): ?Hospital
    {
        return $this->hospital_service->getMappedHospital();
    }

    /**
     * Renders the primary Emergency Department Dashboard (SC-02).
     *
     * @return ResponseInterface|string
     */
    public function dashboard(): string|RedirectResponse
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.logout'))->with('error', 'Your account is not mapped to a hospital facility. Please contact an administrator.');
        }

        $data = [
            'page_title'       => $hospital->name . ' Emergency Department | ClearBay',
            'meta_description' => 'Live ambulance tracking and off-load management dashboard for ' . $hospital->name,
            'canonical_url'    => url_to('hospital.dashboard'),
            'robots_tag'       => 'noindex, nofollow',
            'hospital'         => $hospital,
            'user_role'        => session()->get('user_role'),
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

        $data = $this->hospital_service->getQueueData((int) $hospital->id);

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

        // Role guard: only hospital_admin and admin can modify bay configuration (structural data)
        $user_role = session()->get('user_role');
        if ($user_role !== 'hospital_admin' && $user_role !== 'admin') {
            // Nurses can update status color only, not bay count
            $bays_available = (int) $hospital->bays_available;
        }

        $success = $this->hospital_service->updateStatus((int) $hospital->id, $status, $bays_available, (int) $user_id);

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
     * Marks a handover as "Arrived" and records the arrival timestamp.
     * Accessible to nurses and hospital_admin via the Hospital module.
     *
     * @return ResponseInterface
     */
    public function markArrived(): ResponseInterface
    {
        $rules = [
            'handover_id' => 'required|integer',
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

        // Security guard: verify handover belongs to this user's hospital
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Your account is not mapped to a hospital facility.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $success = $this->hospital_service->markArrived($handover_id);

        if (!$success) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Failed to mark handover as Arrived. Ensure the handover exists and its current status is "En route".',
                'csrf_token' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Handover marked as Arrived successfully.',
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

        $success = $this->hospital_service->completeHandover($handover_id, $bay_number, $notes, (int) $user_id);

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
    public function analytics(): string|RedirectResponse
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.logout'))->with('error', 'Your account is not mapped to a hospital facility. Please contact an administrator.');
        }

        $range = (string) ($this->request->getGet('range') ?? '7');
        $days  = in_array($range, ['7', '30', '90'], true) ? (int) $range : 7;

        $analytics = $this->hospital_service->getAnalytics((int) $hospital->id, $days);

        $data = [
            'page_title'       => 'ED Analytics | ' . $hospital->name,
            'meta_description' => 'Emergency Department ambulance handover statistics and performance.',
            'canonical_url'    => url_to('hospital.analytics'),
            'robots_tag'       => 'noindex, nofollow',
            'hospital'         => $hospital,
            'analytics'        => $analytics,
            'range'            => $range,
        ];

        return view('App\Modules\Hospital\Views\analytics', $data);
    }

    /**
     * Generates a plain-text downloadable CSV report matching SC-06 metrics.
     *
     * @return ResponseInterface
     */
    public function exportPdf(): ResponseInterface
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return $this->response->setBody('Session expired.')->setStatusCode(401);
        }

        $analytics = $this->hospital_service->getAnalytics((int) $hospital->id, 30);

        // Build CSV with proper escaping for spreadsheet compatibility
        $lines   = [];
        $lines[] = "ClearBay Off-Load Performance Report";
        $lines[] = "Hospital," . $this->_csvEscape($hospital->name);
        $lines[] = "Date Range,Past 30 Days";
        $lines[] = "Report Date," . date('Y-m-d H:i:s') . " EAT";
        $lines[] = "";
        $lines[] = "EMS Provider Summary";
        $lines[] = "Provider,Handovers Completed,Average Wait Time (Minutes)";

        foreach ($analytics['provider_performance'] as $row) {
            $lines[] = sprintf(
                "%s,%d,%s",
                $this->_csvEscape((string) $row['provider']),
                (int) $row['total_handovers'],
                (string) $row['avg_wait']
            );
        }

        $content = implode("\n", $lines) . "\n";

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="clearbay_report_' . $hospital->code . '.csv"')
            ->setBody($content);
    }

    // =========================================================================
    // HOSPITAL USER MANAGEMENT (hospital_admin only)
    // =========================================================================

    /**
     * Lists users scoped to the hospital_admin's own hospital.
     *
     * @return string|RedirectResponse
     */
    public function usersList(): string|RedirectResponse
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.logout'))->with('error', 'Your account is not mapped to a hospital facility.');
        }

        $users = $this->hospital_service->getHospitalUsers((int) $hospital->id);

        $data = [
            'page_title'       => 'Manage Users | ' . $hospital->name,
            'meta_description' => 'Manage hospital-specific user accounts.',
            'canonical_url'    => url_to('hospital.users.list'),
            'robots_tag'       => 'noindex, nofollow',
            'users'            => $users,
            'hospital'         => $hospital,
        ];

        return view('App\Modules\Hospital\Views\users\list', $data);
    }

    /**
     * Renders form to create a new hospital-scoped user.
     *
     * @return string|RedirectResponse
     */
    public function userNew(): string|RedirectResponse
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.logout'))->with('error', 'Your account is not mapped to a hospital facility.');
        }

        $data = [
            'page_title'       => 'Add User | ' . $hospital->name,
            'meta_description' => 'Register a new user for your hospital facility.',
            'canonical_url'    => url_to('hospital.users.new'),
            'robots_tag'       => 'noindex, nofollow',
            'hospital'         => $hospital,
        ];

        return view('App\Modules\Hospital\Views\users\edit', $data);
    }

    /**
     * Validates and saves a new hospital-scoped user.
     *
     * @return RedirectResponse
     */
    public function userCreate(): RedirectResponse
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.logout'))->with('error', 'Session expired.');
        }

        $rules = [
            'name'  => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|max_length[255]|is_unique[users.email]',
            'role'  => 'required|in_list[nurse,hospital_admin]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name  = (string) $this->request->getPost('name');
        $email = (string) $this->request->getPost('email');
        $role  = (string) $this->request->getPost('role');

        $success = $this->hospital_service->createHospitalUser($name, $email, $role, (int) $hospital->id);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating user.');
        }

        return redirect()->to(url_to('hospital.users.list'))->with('success', 'User account created successfully with temporary password "12345678"!');
    }

    /**
     * Renders form to edit an existing hospital-scoped user.
     *
     * @param string $user_id
     * @return string|RedirectResponse
     */
    public function userEdit(string $user_id): string|RedirectResponse
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.logout'))->with('error', 'Your account is not mapped to a hospital facility.');
        }

        /** @var \App\Modules\Auth\Entities\User|null $user */
        $user = $this->hospital_service->getHospitalUser((int) $user_id, (int) $hospital->id);

        if ($user === null) {
            return redirect()->to(url_to('hospital.users.list'))->with('error', 'User not found or does not belong to your hospital.');
        }

        $data = [
            'page_title'       => 'Edit User | ' . $hospital->name,
            'meta_description' => 'Modify user account details.',
            'canonical_url'    => url_to('hospital.users.edit', $user_id),
            'robots_tag'       => 'noindex, nofollow',
            'user'             => $user,
            'hospital'         => $hospital,
        ];

        return view('App\Modules\Hospital\Views\users\edit', $data);
    }

    /**
     * Validates and updates an existing hospital-scoped user.
     *
     * @param string $user_id
     * @return RedirectResponse
     */
    public function userUpdate(string $user_id): RedirectResponse
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.logout'))->with('error', 'Session expired.');
        }

        $user = $this->hospital_service->getHospitalUser((int) $user_id, (int) $hospital->id);
        if ($user === null) {
            return redirect()->to(url_to('hospital.users.list'))->with('error', 'User not found or does not belong to your hospital.');
        }

        $rules = [
            'name'         => 'required|min_length[3]|max_length[255]',
            'email'        => 'required|valid_email|max_length[255]|is_unique[users.email,id,' . $user_id . ']',
            'role'         => 'required|in_list[nurse,hospital_admin]',
            'active'       => 'required|in_list[0,1]',
            'new_password' => 'permit_empty|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name         = (string) $this->request->getPost('name');
        $email        = (string) $this->request->getPost('email');
        $role         = (string) $this->request->getPost('role');
        $active       = (int) $this->request->getPost('active');
        $new_password = $this->request->getPost('new_password');

        $success = $this->hospital_service->updateHospitalUser(
            (int) $user_id,
            (int) $hospital->id,
            $name,
            $email,
            $role,
            $active,
            !empty($new_password) ? (string) $new_password : null
        );

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating user.');
        }

        return redirect()->to(url_to('hospital.users.list'))->with('success', 'User account updated successfully!');
    }

    /**
     * Deactivates a hospital-scoped user (soft delete).
     *
     * @param string $user_id
     * @return RedirectResponse
     */
    public function userDelete(string $user_id): RedirectResponse
    {
        $hospital = $this->_getMappedHospital();
        if ($hospital === null) {
            return redirect()->to(url_to('auth.logout'))->with('error', 'Session expired.');
        }

        $success = $this->hospital_service->deleteHospitalUser((int) $user_id, (int) $hospital->id);

        if (!$success) {
            return redirect()->to(url_to('hospital.users.list'))->with('error', 'Database transaction failed while deleting user.');
        }

        return redirect()->to(url_to('hospital.users.list'))->with('success', 'User account deactivated successfully.');
    }

    /**
     * Escapes a string value for safe inclusion in a CSV cell.
     *
     * @param string $value
     * @return string
     */
    private function _csvEscape(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}
