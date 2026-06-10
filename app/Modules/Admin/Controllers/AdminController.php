<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Pilot\Entities\PilotSignup;
use App\Modules\Hospital\Entities\Handover;
use App\Modules\Hospital\Entities\Hospital;
use App\Modules\Ambulance\Entities\Ambulance;
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
     * Declared helpers.
     */
    protected $helpers = ['form', 'url'];

    /**
     * AdminController constructor.
     *
     * @param AdminService|null $admin_service
     */
    public function __construct(?AdminService $admin_service = null)
    {
        $this->admin_service = $admin_service ?? service('adminService');
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
            'pageTitle'       => 'Admin Dashboard | ClearBay',
            'metaDescription' => 'ClearBay administrative management control panel.',
            'canonicalUrl'    => url_to('admin.dashboard'),
            'robotsTag'       => 'noindex, nofollow',
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
            'pageTitle'       => 'Manage Pilot Signups | ClearBay',
            'metaDescription' => 'Review and manage incoming pilot onboarding request records.',
            'canonicalUrl'    => url_to('admin.pilots.list'),
            'robotsTag'       => 'noindex, nofollow',
            'pilots'          => $result['pilots'],
            'pager'           => $result['pager'],
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
            'pageTitle'       => 'Add Pilot Signup | ClearBay',
            'metaDescription' => 'Manually register a new pilot program application.',
            'canonicalUrl'    => url_to('admin.pilots.new'),
            'robotsTag'       => 'noindex, nofollow',
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
            'pageTitle'       => 'Edit Pilot Signup | ClearBay',
            'metaDescription' => 'Modify an existing pilot signup application.',
            'canonicalUrl'    => url_to('admin.pilots.edit', $pilot_id),
            'robotsTag'       => 'noindex, nofollow',
            'pilot'           => $pilot,
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
            'pageTitle'       => 'Manage Handovers | ClearBay',
            'metaDescription' => 'Review and manage ambulance queue handovers.',
            'canonicalUrl'    => url_to('admin.handovers.list'),
            'robotsTag'       => 'noindex, nofollow',
            'handovers'       => $result['handovers'],
            'pager'           => $result['pager'],
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
            'pageTitle'       => 'Add Handover | ClearBay',
            'metaDescription' => 'Register a new active ambulance queue handover.',
            'canonicalUrl'    => url_to('admin.handovers.new'),
            'robotsTag'       => 'noindex, nofollow',
            'hospitals'       => $this->admin_service->getAllHospitals(),
            'ambulances'      => $this->admin_service->getAllAmbulances(),
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
            'bayNumber'       => 'permit_empty|alpha_numeric_space|max_length[50]',
            'notes'           => 'permit_empty|max_length[200]',
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
        $handover->bay_number         = $this->request->getPost('bayNumber') ? (string) $this->request->getPost('bayNumber') : null;
        $handover->notes              = $this->request->getPost('notes') ? (string) $this->request->getPost('notes') : null;

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
            'pageTitle'       => 'Edit Handover | ClearBay',
            'metaDescription' => 'Modify an existing queue handover.',
            'canonicalUrl'    => url_to('admin.handovers.edit', $handover_id),
            'robotsTag'       => 'noindex, nofollow',
            'handover'        => $handover,
            'hospitals'       => $this->admin_service->getAllHospitals(),
            'ambulances'      => $this->admin_service->getAllAmbulances(),
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
            'bayNumber'       => 'permit_empty|alpha_numeric_space|max_length[50]',
            'notes'           => 'permit_empty|max_length[200]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $old_status = $handover->status;
        $new_status = (string) $this->request->getPost('status');

        $handover->ambulance_id       = (int) $this->request->getPost('ambulanceId');
        $handover->hospital_id        = (int) $this->request->getPost('hospitalId');
        $handover->patient_age        = (int) $this->request->getPost('patientAge');
        $handover->patient_gender     = (string) $this->request->getPost('patientGender');
        $handover->acuity             = (string) $this->request->getPost('acuity');
        $handover->eta_minutes        = (int) $this->request->getPost('etaMinutes');
        $handover->wait_time_minutes  = (int) $this->request->getPost('waitTimeMinutes');
        $handover->status             = $new_status;
        $handover->bay_number         = $this->request->getPost('bayNumber') ? (string) $this->request->getPost('bayNumber') : null;
        $handover->notes              = $this->request->getPost('notes') ? (string) $this->request->getPost('notes') : null;

        // Admin-only arrival declaration: record timestamp on transition to 'Arrived'
        if ($old_status === 'En route' && $new_status === 'Arrived') {
            $handover->arrived_at = date('Y-m-d H:i:s');
        }

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
            'pageTitle'       => 'Manage Hospitals | ClearBay',
            'metaDescription' => 'Review and manage partner hospital records.',
            'canonicalUrl'    => url_to('admin.hospitals.list'),
            'robotsTag'       => 'noindex, nofollow',
            'hospitals'       => $result['hospitals'],
            'pager'           => $result['pager'],
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
            'pageTitle'       => 'Add Hospital | ClearBay',
            'metaDescription' => 'Add a new hospital facility profile.',
            'canonicalUrl'    => url_to('admin.hospitals.new'),
            'robotsTag'       => 'noindex, nofollow',
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
            'code'          => 'required|min_length[2]|max_length[10]|is_unique[hospitals.code]',
            'name'          => 'required|min_length[3]|max_length[255]',
            'category'      => 'required|min_length[3]|max_length[255]',
            'status'        => 'required|in_list[Green,Amber,Red,Recruiting]',
            'bays_available' => 'permit_empty|integer|greater_than_equal_to[0]',
            'baseline_avg'  => 'permit_empty|integer|greater_than_equal_to[0]',
            'lat'           => 'permit_empty|decimal',
            'lng'           => 'permit_empty|decimal',
            'address'       => 'permit_empty|max_length[500]',
            'contact_phone' => 'permit_empty|max_length[50]',
            'active'        => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hospital = new Hospital();
        $hospital->code          = strtoupper((string) $this->request->getPost('code'));
        $hospital->name          = (string) $this->request->getPost('name');
        $hospital->category      = (string) $this->request->getPost('category');
        $hospital->status        = (string) $this->request->getPost('status');
        $hospital->bays_available = (int) ($this->request->getPost('bays_available') ?? 0);
        $hospital->baseline_avg  = (int) ($this->request->getPost('baseline_avg') ?? 60);
        $hospital->lat           = $this->request->getPost('lat') !== null ? (float) $this->request->getPost('lat') : null;
        $hospital->lng           = $this->request->getPost('lng') !== null ? (float) $this->request->getPost('lng') : null;
        $hospital->address       = $this->request->getPost('address') ? (string) $this->request->getPost('address') : null;
        $hospital->contact_phone = $this->request->getPost('contact_phone') ? (string) $this->request->getPost('contact_phone') : null;
        $hospital->active        = $this->request->getPost('active') !== null ? (int) $this->request->getPost('active') : 1;

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
            'pageTitle'       => 'Edit Hospital | ClearBay',
            'metaDescription' => 'Modify hospital facility configuration and capacity status.',
            'canonicalUrl'    => url_to('admin.hospitals.edit', $hospital_id),
            'robotsTag'       => 'noindex, nofollow',
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
            'code'          => 'required|min_length[2]|max_length[10]|is_unique[hospitals.code,id,' . $hospital_id . ']',
            'name'          => 'required|min_length[3]|max_length[255]',
            'category'      => 'required|min_length[3]|max_length[255]',
            'status'        => 'required|in_list[Green,Amber,Red,Recruiting]',
            'bays_available' => 'permit_empty|integer|greater_than_equal_to[0]',
            'baseline_avg'  => 'permit_empty|integer|greater_than_equal_to[0]',
            'lat'           => 'permit_empty|decimal',
            'lng'           => 'permit_empty|decimal',
            'address'       => 'permit_empty|max_length[500]',
            'contact_phone' => 'permit_empty|max_length[50]',
            'active'        => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hospital->code          = strtoupper((string) $this->request->getPost('code'));
        $hospital->name          = (string) $this->request->getPost('name');
        $hospital->category      = (string) $this->request->getPost('category');
        $hospital->status        = (string) $this->request->getPost('status');
        $hospital->bays_available = (int) ($this->request->getPost('bays_available') ?? 0);
        $hospital->baseline_avg  = (int) ($this->request->getPost('baseline_avg') ?? 60);
        $hospital->lat           = $this->request->getPost('lat') !== null ? (float) $this->request->getPost('lat') : null;
        $hospital->lng           = $this->request->getPost('lng') !== null ? (float) $this->request->getPost('lng') : null;
        $hospital->address       = $this->request->getPost('address') ? (string) $this->request->getPost('address') : null;
        $hospital->contact_phone = $this->request->getPost('contact_phone') ? (string) $this->request->getPost('contact_phone') : null;
        $hospital->active        = $this->request->getPost('active') !== null ? (int) $this->request->getPost('active') : 1;

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
            'pageTitle'       => 'Manage Ambulances | ClearBay',
            'metaDescription' => 'Review and manage ambulance fleet units.',
            'canonicalUrl'    => url_to('admin.ambulances.list'),
            'robotsTag'       => 'noindex, nofollow',
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
            'pageTitle'       => 'Add Ambulance | ClearBay',
            'metaDescription' => 'Register a new emergency vehicle fleet unit.',
            'canonicalUrl'    => url_to('admin.ambulances.new'),
            'robotsTag'       => 'noindex, nofollow',
            'ems_providers'    => $this->admin_service->getAllEmsProviders(),
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
            'unitId'        => 'required|min_length[3]|max_length[50]|is_unique[ambulances.unit_id]',
            'provider'      => 'required|min_length[2]|max_length[255]',
            'ems_provider_id' => 'permit_empty|integer',
            'registration'  => 'permit_empty|max_length[50]',
            'status'        => 'permit_empty|in_list[Available,Transporting,On Scene,Queued,Off Duty]',
            'current_lat'   => 'permit_empty|decimal',
            'current_lng'   => 'permit_empty|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ambulance = new Ambulance();
        $ambulance->unit_id        = strtoupper((string) $this->request->getPost('unitId'));
        $ambulance->provider       = (string) $this->request->getPost('provider');
        $ambulance->ems_provider_id = $this->request->getPost('ems_provider_id') ? (int) $this->request->getPost('ems_provider_id') : null;
        $ambulance->registration   = $this->request->getPost('registration') ? (string) $this->request->getPost('registration') : null;
        $ambulance->status         = (string) ($this->request->getPost('status') ?? 'Available');
        $ambulance->current_lat    = $this->request->getPost('current_lat') !== null ? (float) $this->request->getPost('current_lat') : null;
        $ambulance->current_lng    = $this->request->getPost('current_lng') !== null ? (float) $this->request->getPost('current_lng') : null;

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
            'pageTitle'       => 'Edit Ambulance | ClearBay',
            'metaDescription' => 'Modify vehicle fleet configuration details.',
            'canonicalUrl'    => url_to('admin.ambulances.edit', $ambulance_id),
            'robotsTag'       => 'noindex, nofollow',
            'ambulance'        => $ambulance,
            'ems_providers'    => $this->admin_service->getAllEmsProviders(),
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
            'unitId'        => 'required|min_length[3]|max_length[50]|is_unique[ambulances.unit_id,id,' . $ambulance_id . ']',
            'provider'      => 'required|min_length[2]|max_length[255]',
            'ems_provider_id' => 'permit_empty|integer',
            'registration'  => 'permit_empty|max_length[50]',
            'status'        => 'permit_empty|in_list[Available,Transporting,On Scene,Queued,Off Duty]',
            'current_lat'   => 'permit_empty|decimal',
            'current_lng'   => 'permit_empty|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ambulance->unit_id        = strtoupper((string) $this->request->getPost('unitId'));
        $ambulance->provider       = (string) $this->request->getPost('provider');
        $ambulance->ems_provider_id = $this->request->getPost('ems_provider_id') ? (int) $this->request->getPost('ems_provider_id') : null;
        $ambulance->registration   = $this->request->getPost('registration') ? (string) $this->request->getPost('registration') : null;
        $ambulance->status         = (string) ($this->request->getPost('status') ?? 'Available');
        $ambulance->current_lat    = $this->request->getPost('current_lat') !== null ? (float) $this->request->getPost('current_lat') : null;
        $ambulance->current_lng    = $this->request->getPost('current_lng') !== null ? (float) $this->request->getPost('current_lng') : null;

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
            'pageTitle'       => 'Manage Users | ClearBay',
            'metaDescription' => 'Review and manage ClearBay operator and staff user accounts.',
            'canonicalUrl'    => url_to('admin.users.list'),
            'robotsTag'       => 'noindex, nofollow',
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
            'pageTitle'       => 'Add User Account | ClearBay',
            'metaDescription' => 'Register a new user profile with specific authorization roles.',
            'canonicalUrl'    => url_to('admin.users.new'),
            'robotsTag'       => 'noindex, nofollow',
            'hospitals'        => $this->admin_service->getAllHospitals(),
            'ems_providers'    => $this->admin_service->getAllEmsProviders(),
            'ambulances'       => $this->_getAmbulancesWithAssignments(),
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
            'ambulance_id'    => 'permit_empty|integer',
            'active'          => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = new User();
        $user->name            = (string) $this->request->getPost('name');
        $user->email           = (string) $this->request->getPost('email');
        $user->password_hash   = password_hash('12345678', PASSWORD_BCRYPT);
        $user->role            = (string) $this->request->getPost('role');
        $user->hospital_id     = $this->request->getPost('hospital_id') ? (int) $this->request->getPost('hospital_id') : null;
        $user->ems_provider_id = $this->request->getPost('ems_provider_id') ? (int) $this->request->getPost('ems_provider_id') : null;
        $user->ambulance_id    = $this->request->getPost('ambulance_id') ? (int) $this->request->getPost('ambulance_id') : null;
        $user->active          = (int) $this->request->getPost('active');

        // Validate ambulance uniqueness: prevent double assignment
        if ($user->ambulance_id !== null) {
            $conflict = $this->_checkAmbulanceConflict($user->ambulance_id, null);
            if ($conflict !== null) {
                return redirect()->back()->withInput()->with('errors', ['ambulance_id' => 'This ambulance is already assigned to ' . $conflict . '.']);
            }
        }

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
            'pageTitle'       => 'Edit User Account | ClearBay',
            'metaDescription' => 'Modify account credentials, role levels, and active states.',
            'canonicalUrl'    => url_to('admin.users.edit', $user_id),
            'robotsTag'       => 'noindex, nofollow',
            'user'             => $user,
            'hospitals'        => $this->admin_service->getAllHospitals(),
            'ems_providers'    => $this->admin_service->getAllEmsProviders(),
            'ambulances'       => $this->_getAmbulancesWithAssignments((int) $user_id),
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
            'ambulance_id'    => 'permit_empty|integer',
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
        $user->ambulance_id    = $this->request->getPost('ambulance_id') ? (int) $this->request->getPost('ambulance_id') : null;
        $user->active          = (int) $this->request->getPost('active');

        // Validate ambulance uniqueness: prevent double assignment
        if ($user->ambulance_id !== null) {
            $conflict = $this->_checkAmbulanceConflict($user->ambulance_id, (int) $user_id);
            if ($conflict !== null) {
                return redirect()->back()->withInput()->with('errors', ['ambulance_id' => 'This ambulance is already assigned to ' . $conflict . '.']);
            }
        }

        // Optional set new password if field is not empty
        $new_password = $this->request->getPost('new_password');
        if (!empty($new_password)) {
            $password_rules = [
                'new_password' => 'required|min_length[6]',
            ];
            if (!$this->validate($password_rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
            $user->password_hash = password_hash((string) $new_password, PASSWORD_BCRYPT);
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

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Retrieves all ambulances annotated with their current paramedic assignment.
     * Each row includes 'assigned_to_name' and 'assigned_to_id' if an active
     * paramedic user already holds this ambulance.
     *
     * @param int|null $exclude_user_id Exclude this user from the conflict check (for edit forms).
     * @return array
     */
    private function _getAmbulancesWithAssignments(?int $exclude_user_id = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('ambulances')
            ->select('ambulances.id, ambulances.unit_id, ambulances.provider, ambulances.ems_provider_id, ambulances.registration, ambulances.status, users.id as assigned_to_id, users.name as assigned_to_name')
            ->join('users', 'users.ambulance_id = ambulances.id AND users.active = 1 AND users.role = "paramedic"', 'left')
            ->orderBy('ambulances.unit_id', 'ASC');

        // When editing a specific user, exclude them so their own assignment doesn't show as a conflict
        if ($exclude_user_id !== null) {
            $builder->where('(users.id IS NULL OR users.id = ' . (int) $exclude_user_id . ')');
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Checks if an ambulance is already assigned to another active paramedic.
     * Returns the conflicting user's name, or null if no conflict.
     *
     * @param int      $ambulance_id
     * @param int|null $current_user_id The user being edited (excluded from conflict).
     * @return string|null
     */
    private function _checkAmbulanceConflict(int $ambulance_id, ?int $current_user_id): ?string
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users')
            ->select('name')
            ->where('ambulance_id', $ambulance_id)
            ->where('active', 1)
            ->where('role', 'paramedic');

        if ($current_user_id !== null) {
            $builder->where('id !=', $current_user_id);
        }

        $row = $builder->get()->getRow();
        return $row ? $row->name : null;
    }

    /**
     * Renders the Global Analytics Dashboard (SC-06) for Sysadmin.
     *
     * @return RedirectResponse|string
     */
    public function analytics(): string|RedirectResponse
    {
        try {
            $hospital_service = service('hospitalService');

            $range = (string) ($this->request->getGet('range') ?? '7');
            $days  = in_array($range, ['7', '30', '90'], true) ? (int) $range : 7;

            // Fetch global analytics (hospital_id = null)
            $analytics = $hospital_service->getAnalytics(null, $days);

            $data = [
                'pageTitle'       => 'System Analytics | ClearBay',
                'metaDescription' => 'System-wide ambulance handover statistics and facility performance.',
                'canonicalUrl'    => url_to('admin.analytics'),
                'robotsTag'       => 'noindex, nofollow',
                'analytics'        => $analytics,
                'range'            => $range,
            ];

            return view('App\Modules\Admin\Views\analytics', $data);
        } catch (\Throwable $e) {
            log_message('error', 'Exception in AdminController::analytics', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return redirect()->to(url_to('admin.dashboard'))->with('error', 'An internal server error occurred while rendering analytics.');
        }
    }

    /**
     * Generates a plain-text downloadable CSV report matching global metrics.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function exportPdf(): \CodeIgniter\HTTP\ResponseInterface
    {
        $hospital_service = service('hospitalService');
        $analytics = $hospital_service->getAnalytics(null, 30);

        // Build CSV with proper escaping for spreadsheet compatibility
        $lines   = [];
        $lines[] = "ClearBay Global Off-Load Performance Report";
        $lines[] = "Facility,All Facilities";
        $lines[] = "Date Range,Past 30 Days";
        $lines[] = "Report Date," . date('Y-m-d H:i:s') . " EAT";
        $lines[] = "";
        
        // Hospital Breakdown
        $lines[] = "Facility Summary";
        $lines[] = "Hospital Name,Handovers Completed,Average Wait Time (Minutes)";
        foreach ($analytics['facility_performance'] as $row) {
            $lines[] = sprintf(
                "%s,%d,%s",
                $this->_csvEscape((string) $row['hospital_name']),
                (int) $row['total_handovers'],
                (string) $row['avg_wait']
            );
        }
        $lines[] = "";

        // Provider Breakdown
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
            ->setHeader('Content-Disposition', 'attachment; filename="clearbay_global_report.csv"')
            ->setBody($content);
    }

    /**
     * CSV cell escaping utility.
     */
    private function _csvEscape(string $val): string
    {
        $val = str_replace('"', '""', $val);
        if (str_contains($val, ',') || str_contains($val, '"') || str_contains($val, "\n") || str_contains($val, "\r")) {
            return '"' . $val . '"';
        }
        return $val;
    }
}
