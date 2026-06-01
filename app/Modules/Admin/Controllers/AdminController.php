<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Pilot\Entities\PilotSignup;
use App\Modules\Queue\Entities\Handover;
use App\Modules\Queue\Entities\Hospital;
use App\Modules\Queue\Entities\Ambulance;
use App\Modules\Auth\Entities\User;
use CodeIgniter\HTTP\RedirectResponse;
use App\Modules\Admin\Libraries\AdminService;

/**
 * Class AdminController
 *
 * Orchestrates operations in the administration dashboard and manages
 * CRUD interfaces for all key operation models.
 *
 * @package App\Modules\Admin\Controllers
 */
class AdminController extends BaseController
{
    /**
     * @var AdminService
     */
    private AdminService $admin_service;

    /**
     * AdminController constructor.
     *
     * @param AdminService|null $admin_service
     */
    public function __construct(?AdminService $admin_service = null)
    {
        $this->admin_service = $admin_service ?? new AdminService();
        helper(['form', 'url']);
    }

    /**
     * Renders the administrative dashboard.
     *
     * @return string
     */
    public function dashboard(): string
    {
        $metrics = $this->admin_service->getDashboardMetrics();
        $data = [
            'page_title'       => 'Admin Dashboard | ClearBay',
            'meta_description' => 'ClearBay administrative management control panel.',
            'canonical_url'    => url_to('admin.dashboard'),
            'robots_tag'       => 'noindex, nofollow',
            'pilotCount'       => $metrics['pilotCount'],
            'handoverCount'    => $metrics['handoverCount'],
            'hospitalCount'    => $metrics['hospitalCount'],
            'ambulanceCount'   => $metrics['ambulanceCount'],
            'userCount'        => $metrics['userCount'],
        ];

        return view('App\Modules\Admin\Views\dashboard', $data);
    }

    // =========================================================================
    // PILOTS CRUD ACTIONS
    // =========================================================================

    /**
     * Lists pilot program signups.
     *
     * @return string
     */
    public function pilotsList(): string
    {
        $result = $this->admin_service->getPilotsList(15);
        $data = [
            'page_title'       => 'Manage Pilot Signups | ClearBay',
            'meta_description' => 'Review and manage incoming pilot onboarding request records.',
            'canonical_url'    => url_to('admin.pilots.list'),
            'robots_tag'       => 'noindex, nofollow',
            'pilots'           => $result['pilots'],
            'pager'            => $result['pager'],
        ];

        return view('App\Modules\Admin\Views\pilots\list', $data);
    }

    /**
     * Renders form to create a new pilot signup record.
     *
     * @return string
     */
    public function pilotNew(): string
    {
        $data = [
            'page_title'       => 'Add Pilot Signup | ClearBay',
            'meta_description' => 'Manually register a new pilot program application.',
            'canonical_url'    => url_to('admin.pilots.new'),
            'robots_tag'       => 'noindex, nofollow',
        ];

        return view('App\Modules\Admin\Views\pilots\edit', $data);
    }

    /**
     * Validates and saves a new pilot signup record.
     *
     * @return RedirectResponse
     */
    public function pilotCreate(): RedirectResponse
    {
        $rules = [
            'fullName'     => 'required|min_length[3]|max_length[255]',
            'emailAddress' => 'required|valid_email|max_length[255]',
            'organisation' => 'required|min_length[3]|max_length[255]',
            'userRole'     => 'required|in_list[Hospital Administrator,ED Manager / Charge Nurse,Emergency Physician,Paramedic / EMT,EMS Dispatcher / Operations Manager,Investor / Funder,Researcher / Academic,Other]',
            'phoneNumber'  => 'permit_empty|min_length[7]|max_length[50]',
            'message'      => 'permit_empty|max_length[2000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $pilot = new PilotSignup();
        $pilot->full_name     = (string) $this->request->getPost('fullName');
        $pilot->email_address = (string) $this->request->getPost('emailAddress');
        $pilot->organisation  = (string) $this->request->getPost('organisation');
        $pilot->user_role     = (string) $this->request->getPost('userRole');
        $pilot->phone_number  = $this->request->getPost('phoneNumber') ? (string) $this->request->getPost('phoneNumber') : null;
        $pilot->message       = $this->request->getPost('message') ? (string) $this->request->getPost('message') : null;

        $success = $this->admin_service->savePilot($pilot);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating signup.');
        }

        return redirect()->to(url_to('admin.pilots.list'))->with('success', 'Pilot program signup added successfully!');
    }

    /**
     * Renders form to edit an existing pilot signup record.
     *
     * @param string $pilot_id
     * @return string|RedirectResponse
     */
    public function pilotEdit(string $pilot_id): string|RedirectResponse
    {
        /** @var PilotSignup|null $pilot */
        $pilot = $this->admin_service->getPilot((int) $pilot_id);

        if (!$pilot) {
            return redirect()->to(url_to('admin.pilots.list'))->with('error', 'Requested pilot signup record not found.');
        }

        $data = [
            'page_title'       => 'Edit Pilot Signup | ClearBay',
            'meta_description' => 'Modify an existing pilot signup application.',
            'canonical_url'    => url_to('admin.pilots.edit', $pilot_id),
            'robots_tag'       => 'noindex, nofollow',
            'pilot'            => $pilot,
        ];

        return view('App\Modules\Admin\Views\pilots\edit', $data);
    }

    /**
     * Validates and updates an existing pilot signup record.
     *
     * @param string $pilot_id
     * @return RedirectResponse
     */
    public function pilotUpdate(string $pilot_id): RedirectResponse
    {
        /** @var PilotSignup|null $pilot */
        $pilot = $this->admin_service->getPilot((int) $pilot_id);

        if (!$pilot) {
            return redirect()->to(url_to('admin.pilots.list'))->with('error', 'Pilot signup record not found.');
        }

        $rules = [
            'fullName'     => 'required|min_length[3]|max_length[255]',
            'emailAddress' => 'required|valid_email|max_length[255]',
            'organisation' => 'required|min_length[3]|max_length[255]',
            'userRole'     => 'required|in_list[Hospital Administrator,ED Manager / Charge Nurse,Emergency Physician,Paramedic / EMT,EMS Dispatcher / Operations Manager,Investor / Funder,Researcher / Academic,Other]',
            'phoneNumber'  => 'permit_empty|min_length[7]|max_length[50]',
            'message'      => 'permit_empty|max_length[2000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $pilot->full_name     = (string) $this->request->getPost('fullName');
        $pilot->email_address = (string) $this->request->getPost('emailAddress');
        $pilot->organisation  = (string) $this->request->getPost('organisation');
        $pilot->user_role     = (string) $this->request->getPost('userRole');
        $pilot->phone_number  = $this->request->getPost('phoneNumber') ? (string) $this->request->getPost('phoneNumber') : null;
        $pilot->message       = $this->request->getPost('message') ? (string) $this->request->getPost('message') : null;

        $success = $this->admin_service->savePilot($pilot);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating signup.');
        }

        return redirect()->to(url_to('admin.pilots.list'))->with('success', 'Pilot program signup updated successfully!');
    }

    /**
     * Deletes a pilot signup record.
     *
     * @param string $pilot_id
     * @return RedirectResponse
     */
    public function pilotDelete(string $pilot_id): RedirectResponse
    {
        $success = $this->admin_service->deletePilot((int) $pilot_id);

        if (!$success) {
            return redirect()->to(url_to('admin.pilots.list'))->with('error', 'Database transaction failed while deleting signup.');
        }

        return redirect()->to(url_to('admin.pilots.list'))->with('success', 'Pilot program signup deleted successfully.');
    }

    // =========================================================================
    // HANDOVERS CRUD ACTIONS
    // =========================================================================

    /**
     * Lists handovers.
     *
     * @return string
     */
    public function handoversList(): string
    {
        $result = $this->admin_service->getHandoversList(15);
        $data = [
            'page_title'       => 'Manage Handovers | ClearBay',
            'meta_description' => 'Review and manage ambulance queue handovers.',
            'canonical_url'    => url_to('admin.handovers.list'),
            'robots_tag'       => 'noindex, nofollow',
            'handovers'        => $result['handovers'],
            'pager'            => $result['pager'],
        ];

        return view('App\Modules\Admin\Views\handovers\list', $data);
    }

    /**
     * Renders form to create a new handover record.
     *
     * @return string
     */
    public function handoverNew(): string
    {
        $data = [
            'page_title'       => 'Add Handover | ClearBay',
            'meta_description' => 'Register a new active ambulance queue handover.',
            'canonical_url'    => url_to('admin.handovers.new'),
            'robots_tag'       => 'noindex, nofollow',
            'hospitals'        => $this->admin_service->getAllHospitals(),
            'ambulances'       => $this->admin_service->getAllAmbulances(),
        ];

        return view('App\Modules\Admin\Views\handovers\edit', $data);
    }

    /**
     * Validates and saves a new handover record.
     *
     * @return RedirectResponse
     */
    public function handoverCreate(): RedirectResponse
    {
        $rules = [
            'ambulanceId'     => 'required|integer',
            'hospitalId'      => 'required|integer',
            'patientAge'      => 'required|integer|greater_than_equal_to[0]',
            'patientGender'   => 'required|in_list[M,F]',
            'acuity'          => 'required|in_list[Critical,Serious,Stable]',
            'etaMinutes'      => 'required|integer|greater_than_equal_to[0]',
            'waitTimeMinutes' => 'required|integer|greater_than_equal_to[0]',
            'status'          => 'required|in_list[En route,Arrived,Acknowledged,Preparing,Cleared]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $handover = new Handover();
        $handover->ambulance_id       = (int) $this->request->getPost('ambulanceId');
        $handover->hospital_id        = (int) $this->request->getPost('hospitalId');
        $handover->patient_age        = (int) $this->request->getPost('patientAge');
        $handover->patient_gender     = (string) $this->request->getPost('patientGender');
        $handover->acuity             = (string) $this->request->getPost('acuity');
        $handover->eta_minutes        = (int) $this->request->getPost('etaMinutes');
        $handover->wait_time_minutes  = (int) $this->request->getPost('waitTimeMinutes');
        $handover->status             = (string) $this->request->getPost('status');

        $success = $this->admin_service->saveHandover($handover);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating handover.');
        }

        return redirect()->to(url_to('admin.handovers.list'))->with('success', 'Handover added successfully!');
    }

    /**
     * Renders form to edit an existing handover record.
     *
     * @param string $handover_id
     * @return string|RedirectResponse
     */
    public function handoverEdit(string $handover_id): string|RedirectResponse
    {
        /** @var Handover|null $handover */
        $handover = $this->admin_service->getHandover((int) $handover_id);

        if (!$handover) {
            return redirect()->to(url_to('admin.handovers.list'))->with('error', 'Requested handover record not found.');
        }

        $data = [
            'page_title'       => 'Edit Handover | ClearBay',
            'meta_description' => 'Modify an existing queue handover.',
            'canonical_url'    => url_to('admin.handovers.edit', $handover_id),
            'robots_tag'       => 'noindex, nofollow',
            'handover'         => $handover,
            'hospitals'        => $this->admin_service->getAllHospitals(),
            'ambulances'       => $this->admin_service->getAllAmbulances(),
        ];

        return view('App\Modules\Admin\Views\handovers\edit', $data);
    }

    /**
     * Validates and updates an existing handover record.
     *
     * @param string $handover_id
     * @return RedirectResponse
     */
    public function handoverUpdate(string $handover_id): RedirectResponse
    {
        /** @var Handover|null $handover */
        $handover = $this->admin_service->getHandover((int) $handover_id);

        if (!$handover) {
            return redirect()->to(url_to('admin.handovers.list'))->with('error', 'Handover record not found.');
        }

        $rules = [
            'ambulanceId'     => 'required|integer',
            'hospitalId'      => 'required|integer',
            'patientAge'      => 'required|integer|greater_than_equal_to[0]',
            'patientGender'   => 'required|in_list[M,F]',
            'acuity'          => 'required|in_list[Critical,Serious,Stable]',
            'etaMinutes'      => 'required|integer|greater_than_equal_to[0]',
            'waitTimeMinutes' => 'required|integer|greater_than_equal_to[0]',
            'status'          => 'required|in_list[En route,Arrived,Acknowledged,Preparing,Cleared]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $handover->ambulance_id       = (int) $this->request->getPost('ambulanceId');
        $handover->hospital_id        = (int) $this->request->getPost('hospitalId');
        $handover->patient_age        = (int) $this->request->getPost('patientAge');
        $handover->patient_gender     = (string) $this->request->getPost('patientGender');
        $handover->acuity             = (string) $this->request->getPost('acuity');
        $handover->eta_minutes        = (int) $this->request->getPost('etaMinutes');
        $handover->wait_time_minutes  = (int) $this->request->getPost('waitTimeMinutes');
        $handover->status             = (string) $this->request->getPost('status');

        $success = $this->admin_service->saveHandover($handover);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating handover.');
        }

        return redirect()->to(url_to('admin.handovers.list'))->with('success', 'Handover updated successfully!');
    }

    /**
     * Deletes a handover record.
     *
     * @param string $handover_id
     * @return RedirectResponse
     */
    public function handoverDelete(string $handover_id): RedirectResponse
    {
        $success = $this->admin_service->deleteHandover((int) $handover_id);

        if (!$success) {
            return redirect()->to(url_to('admin.handovers.list'))->with('error', 'Database transaction failed while deleting handover.');
        }

        return redirect()->to(url_to('admin.handovers.list'))->with('success', 'Handover deleted successfully.');
    }

    // =========================================================================
    // HOSPITALS CRUD ACTIONS
    // =========================================================================

    /**
     * Lists hospitals.
     *
     * @return string
     */
    public function hospitalsList(): string
    {
        $result = $this->admin_service->getHospitalsList(15);
        $data = [
            'page_title'       => 'Manage Hospitals | ClearBay',
            'meta_description' => 'Review and manage partner hospital records.',
            'canonical_url'    => url_to('admin.hospitals.list'),
            'robots_tag'       => 'noindex, nofollow',
            'hospitals'        => $result['hospitals'],
            'pager'            => $result['pager'],
        ];

        return view('App\Modules\Admin\Views\hospitals\list', $data);
    }

    /**
     * Renders form to create a new hospital record.
     *
     * @return string
     */
    public function hospitalNew(): string
    {
        $data = [
            'page_title'       => 'Add Hospital | ClearBay',
            'meta_description' => 'Add a new hospital facility profile.',
            'canonical_url'    => url_to('admin.hospitals.new'),
            'robots_tag'       => 'noindex, nofollow',
        ];

        return view('App\Modules\Admin\Views\hospitals\edit', $data);
    }

    /**
     * Validates and saves a new hospital record.
     *
     * @return RedirectResponse
     */
    public function hospitalCreate(): RedirectResponse
    {
        $rules = [
            'code'     => 'required|min_length[2]|max_length[10]|is_unique[hospitals.code]',
            'name'     => 'required|min_length[3]|max_length[255]',
            'category' => 'required|min_length[3]|max_length[255]',
            'status'   => 'required|in_list[Green,Amber,Red,Recruiting]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hospital = new Hospital();
        $hospital->code     = strtoupper((string) $this->request->getPost('code'));
        $hospital->name     = (string) $this->request->getPost('name');
        $hospital->category = (string) $this->request->getPost('category');
        $hospital->status   = (string) $this->request->getPost('status');

        $success = $this->admin_service->saveHospital($hospital);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating hospital.');
        }

        return redirect()->to(url_to('admin.hospitals.list'))->with('success', 'Hospital facility added successfully!');
    }

    /**
     * Renders form to edit an existing hospital record.
     *
     * @param string $hospital_id
     * @return string|RedirectResponse
     */
    public function hospitalEdit(string $hospital_id): string|RedirectResponse
    {
        /** @var Hospital|null $hospital */
        $hospital = $this->admin_service->getHospital((int) $hospital_id);

        if (!$hospital) {
            return redirect()->to(url_to('admin.hospitals.list'))->with('error', 'Requested hospital record not found.');
        }

        $data = [
            'page_title'       => 'Edit Hospital | ClearBay',
            'meta_description' => 'Modify hospital facility configuration and capacity status.',
            'canonical_url'    => url_to('admin.hospitals.edit', $hospital_id),
            'robots_tag'       => 'noindex, nofollow',
            'hospital'         => $hospital,
        ];

        return view('App\Modules\Admin\Views\hospitals\edit', $data);
    }

    /**
     * Validates and updates an existing hospital record.
     *
     * @param string $hospital_id
     * @return RedirectResponse
     */
    public function hospitalUpdate(string $hospital_id): RedirectResponse
    {
        /** @var Hospital|null $hospital */
        $hospital = $this->admin_service->getHospital((int) $hospital_id);

        if (!$hospital) {
            return redirect()->to(url_to('admin.hospitals.list'))->with('error', 'Hospital record not found.');
        }

        $rules = [
            'code'     => 'required|min_length[2]|max_length[10]|is_unique[hospitals.code,id,' . $hospital_id . ']',
            'name'     => 'required|min_length[3]|max_length[255]',
            'category' => 'required|min_length[3]|max_length[255]',
            'status'   => 'required|in_list[Green,Amber,Red,Recruiting]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hospital->code     = strtoupper((string) $this->request->getPost('code'));
        $hospital->name     = (string) $this->request->getPost('name');
        $hospital->category = (string) $this->request->getPost('category');
        $hospital->status   = (string) $this->request->getPost('status');

        $success = $this->admin_service->saveHospital($hospital);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating hospital.');
        }

        return redirect()->to(url_to('admin.hospitals.list'))->with('success', 'Hospital updated successfully!');
    }

    /**
     * Deletes a hospital record.
     *
     * @param string $hospital_id
     * @return RedirectResponse
     */
    public function hospitalDelete(string $hospital_id): RedirectResponse
    {
        $success = $this->admin_service->deleteHospital((int) $hospital_id);

        if (!$success) {
            return redirect()->to(url_to('admin.hospitals.list'))->with('error', 'Database transaction failed while deleting hospital.');
        }

        return redirect()->to(url_to('admin.hospitals.list'))->with('success', 'Hospital facility deleted successfully.');
    }

    // =========================================================================
    // AMBULANCES CRUD ACTIONS
    // =========================================================================

    /**
     * Lists ambulances.
     *
     * @return string
     */
    public function ambulancesList(): string
    {
        $result = $this->admin_service->getAmbulancesList(15);
        $data = [
            'page_title'       => 'Manage Ambulances | ClearBay',
            'meta_description' => 'Review and manage ambulance fleet units.',
            'canonical_url'    => url_to('admin.ambulances.list'),
            'robots_tag'       => 'noindex, nofollow',
            'ambulances'       => $result['ambulances'],
            'pager'            => $result['pager'],
        ];

        return view('App\Modules\Admin\Views\ambulances\list', $data);
    }

    /**
     * Renders form to create a new ambulance record.
     *
     * @return string
     */
    public function ambulanceNew(): string
    {
        $data = [
            'page_title'       => 'Add Ambulance | ClearBay',
            'meta_description' => 'Register a new emergency vehicle fleet unit.',
            'canonical_url'    => url_to('admin.ambulances.new'),
            'robots_tag'       => 'noindex, nofollow',
        ];

        return view('App\Modules\Admin\Views\ambulances\edit', $data);
    }

    /**
     * Validates and saves a new ambulance record.
     *
     * @return RedirectResponse
     */
    public function ambulanceCreate(): RedirectResponse
    {
        $rules = [
            'unitId'   => 'required|min_length[3]|max_length[50]|is_unique[ambulances.unit_id]',
            'provider' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ambulance = new Ambulance();
        $ambulance->unit_id  = strtoupper((string) $this->request->getPost('unitId'));
        $ambulance->provider = (string) $this->request->getPost('provider');

        $success = $this->admin_service->saveAmbulance($ambulance);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating ambulance.');
        }

        return redirect()->to(url_to('admin.ambulances.list'))->with('success', 'Ambulance vehicle registered successfully!');
    }

    /**
     * Renders form to edit an existing ambulance record.
     *
     * @param string $ambulance_id
     * @return string|RedirectResponse
     */
    public function ambulanceEdit(string $ambulance_id): string|RedirectResponse
    {
        /** @var Ambulance|null $ambulance */
        $ambulance = $this->admin_service->getAmbulance((int) $ambulance_id);

        if (!$ambulance) {
            return redirect()->to(url_to('admin.ambulances.list'))->with('error', 'Requested ambulance record not found.');
        }

        $data = [
            'page_title'       => 'Edit Ambulance | ClearBay',
            'meta_description' => 'Modify vehicle fleet configuration details.',
            'canonical_url'    => url_to('admin.ambulances.edit', $ambulance_id),
            'robots_tag'       => 'noindex, nofollow',
            'ambulance'        => $ambulance,
        ];

        return view('App\Modules\Admin\Views\ambulances\edit', $data);
    }

    /**
     * Validates and updates an existing ambulance record.
     *
     * @param string $ambulance_id
     * @return RedirectResponse
     */
    public function ambulanceUpdate(string $ambulance_id): RedirectResponse
    {
        /** @var Ambulance|null $ambulance */
        $ambulance = $this->admin_service->getAmbulance((int) $ambulance_id);

        if (!$ambulance) {
            return redirect()->to(url_to('admin.ambulances.list'))->with('error', 'Ambulance record not found.');
        }

        $rules = [
            'unitId'   => 'required|min_length[3]|max_length[50]|is_unique[ambulances.unit_id,id,' . $ambulance_id . ']',
            'provider' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ambulance->unit_id  = strtoupper((string) $this->request->getPost('unitId'));
        $ambulance->provider = (string) $this->request->getPost('provider');

        $success = $this->admin_service->saveAmbulance($ambulance);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating ambulance.');
        }

        return redirect()->to(url_to('admin.ambulances.list'))->with('success', 'Ambulance configuration updated successfully!');
    }

    /**
     * Deletes an ambulance record.
     *
     * @param string $ambulance_id
     * @return RedirectResponse
     */
    public function ambulanceDelete(string $ambulance_id): RedirectResponse
    {
        $success = $this->admin_service->deleteAmbulance((int) $ambulance_id);

        if (!$success) {
            return redirect()->to(url_to('admin.ambulances.list'))->with('error', 'Database transaction failed while deleting ambulance.');
        }

        return redirect()->to(url_to('admin.ambulances.list'))->with('success', 'Ambulance record deleted successfully.');
    }

    // =========================================================================
    // USERS CRUD ACTIONS (SC-16)
    // =========================================================================

    /**
     * Lists administrative user accounts.
     *
     * @return string
     */
    public function usersList(): string
    {
        $result = $this->admin_service->getUsersList(15);
        $data = [
            'page_title'       => 'Manage Users | ClearBay',
            'meta_description' => 'Review and manage ClearBay operator and staff user accounts.',
            'canonical_url'    => url_to('admin.users.list'),
            'robots_tag'       => 'noindex, nofollow',
            'users'            => $result['users'],
            'pager'            => $result['pager'],
        ];

        return view('App\Modules\Admin\Views\users\list', $data);
    }

    /**
     * Renders form to manually register a new user account.
     *
     * @return string
     */
    public function userNew(): string
    {
        $data = [
            'page_title'       => 'Add User Account | ClearBay',
            'meta_description' => 'Register a new user profile with specific authorization roles.',
            'canonical_url'    => url_to('admin.users.new'),
            'robots_tag'       => 'noindex, nofollow',
            'hospitals'        => $this->admin_service->getAllHospitals(),
            'ems_providers'    => $this->admin_service->getAllEmsProviders(),
        ];

        return view('App\Modules\Admin\Views\users\edit', $data);
    }

    /**
     * Validates and saves a new user account with temporary password.
     *
     * @return RedirectResponse
     */
    public function userCreate(): RedirectResponse
    {
        $rules = [
            'name'            => 'required|min_length[3]|max_length[255]',
            'email'           => 'required|valid_email|max_length[255]|is_unique[users.email]',
            'role'            => 'required|in_list[nurse,hospital_admin,paramedic,dispatcher,admin]',
            'hospital_id'     => 'permit_empty|integer',
            'ems_provider_id' => 'permit_empty|integer',
            'active'          => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = new User();
        $user->name            = (string) $this->request->getPost('name');
        $user->email           = (string) $this->request->getPost('email');
        $user->password_hash   = password_hash('12345678', PASSWORD_BCRYPT); // Temp default password
        $user->role            = (string) $this->request->getPost('role');
        $user->hospital_id     = $this->request->getPost('hospital_id') ? (int) $this->request->getPost('hospital_id') : null;
        $user->ems_provider_id = $this->request->getPost('ems_provider_id') ? (int) $this->request->getPost('ems_provider_id') : null;
        $user->active          = (int) $this->request->getPost('active');

        $success = $this->admin_service->saveUser($user);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating user.');
        }

        return redirect()->to(url_to('admin.users.list'))->with('success', 'User account registered successfully with temporary password "12345678"!');
    }

    /**
     * Renders form to edit an existing user account.
     *
     * @param string $user_id
     * @return string|RedirectResponse
     */
    public function userEdit(string $user_id): string|RedirectResponse
    {
        /** @var User|null $user */
        $user = $this->admin_service->getUser((int) $user_id);

        if (!$user) {
            return redirect()->to(url_to('admin.users.list'))->with('error', 'Requested user account not found.');
        }

        $data = [
            'page_title'       => 'Edit User Account | ClearBay',
            'meta_description' => 'Modify account credentials, role levels, and active states.',
            'canonical_url'    => url_to('admin.users.edit', $user_id),
            'robots_tag'       => 'noindex, nofollow',
            'user'             => $user,
            'hospitals'        => $this->admin_service->getAllHospitals(),
            'ems_providers'    => $this->admin_service->getAllEmsProviders(),
        ];

        return view('App\Modules\Admin\Views\users\edit', $data);
    }

    /**
     * Validates and updates an existing user account.
     *
     * @param string $user_id
     * @return RedirectResponse
     */
    public function userUpdate(string $user_id): RedirectResponse
    {
        /** @var User|null $user */
        $user = $this->admin_service->getUser((int) $user_id);

        if (!$user) {
            return redirect()->to(url_to('admin.users.list'))->with('error', 'User account not found.');
        }

        $rules = [
            'name'            => 'required|min_length[3]|max_length[255]',
            'email'           => 'required|valid_email|max_length[255]|is_unique[users.email,id,' . $user_id . ']',
            'role'            => 'required|in_list[nurse,hospital_admin,paramedic,dispatcher,admin]',
            'hospital_id'     => 'permit_empty|integer',
            'ems_provider_id' => 'permit_empty|integer',
            'active'          => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user->name            = (string) $this->request->getPost('name');
        $user->email           = (string) $this->request->getPost('email');
        $user->role            = (string) $this->request->getPost('role');
        $user->hospital_id     = $this->request->getPost('hospital_id') ? (int) $this->request->getPost('hospital_id') : null;
        $user->ems_provider_id = $this->request->getPost('ems_provider_id') ? (int) $this->request->getPost('ems_provider_id') : null;
        $user->active          = (int) $this->request->getPost('active');

        // Optional reset password if check box selected
        if ($this->request->getPost('reset_password')) {
            $user->password_hash = password_hash('12345678', PASSWORD_BCRYPT);
        }

        $success = $this->admin_service->saveUser($user);

        if (!$success) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating user.');
        }

        return redirect()->to(url_to('admin.users.list'))->with('success', 'User account updated successfully!');
    }

    /**
     * Deactivates or deletes a user account.
     *
     * @param string $user_id
     * @return RedirectResponse
     */
    public function userDelete(string $user_id): RedirectResponse
    {
        $success = $this->admin_service->deleteUser((int) $user_id);

        if (!$success) {
            return redirect()->to(url_to('admin.users.list'))->with('error', 'Database transaction failed while deleting user.');
        }

        return redirect()->to(url_to('admin.users.list'))->with('success', 'User account deleted successfully.');
    }
}
