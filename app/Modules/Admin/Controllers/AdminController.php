<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Pilot\Entities\PilotSignup;
use App\Modules\Pilot\Models\PilotSignupModel;
use App\Modules\Queue\Entities\Handover;
use App\Modules\Queue\Models\HandoverModel;
use App\Modules\Queue\Entities\Hospital;
use App\Modules\Queue\Models\HospitalModel;
use App\Modules\Queue\Entities\Ambulance;
use App\Modules\Queue\Models\AmbulanceModel;
use App\Modules\Auth\Entities\User;
use App\Modules\Auth\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

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
     * @var PilotSignupModel
     */
    private PilotSignupModel $_pilot_model;

    /**
     * @var HandoverModel
     */
    private HandoverModel $_handover_model;

    /**
     * @var HospitalModel
     */
    private HospitalModel $_hospital_model;

    /**
     * @var AmbulanceModel
     */
    private AmbulanceModel $_ambulance_model;

    /**
     * @var UserModel
     */
    private UserModel $_user_model;

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        $this->_pilot_model = new PilotSignupModel();
        $this->_handover_model = new HandoverModel();
        $this->_hospital_model = new HospitalModel();
        $this->_ambulance_model = new AmbulanceModel();
        $this->_user_model = new UserModel();
        helper(['form', 'url']);
    }

    /**
     * Renders the administrative dashboard.
     *
     * @return string
     */
    public function dashboard(): string
    {
        $data = [
            'pageTitle'       => 'Admin Dashboard | ClearBay',
            'metaDescription' => 'ClearBay administrative management control panel.',
            'canonicalUrl'    => url_to('admin.dashboard'),
            'robotsTag'       => 'noindex, nofollow',
            'pilotCount'      => $this->_pilot_model->countAllResults(),
            'handoverCount'   => $this->_handover_model->countAllResults(),
            'hospitalCount'   => $this->_hospital_model->countAllResults(),
            'ambulanceCount'  => $this->_ambulance_model->countAllResults(),
            'userCount'       => $this->_user_model->countAllResults(),
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
        $data = [
            'pageTitle'       => 'Manage Pilot Signups | ClearBay',
            'metaDescription' => 'Review and manage incoming pilot onboarding request records.',
            'canonicalUrl'    => url_to('admin.pilots.list'),
            'robotsTag'       => 'noindex, nofollow',
            'pilots'          => $this->_pilot_model->orderBy('created_at', 'DESC')->paginate(15, 'pilots'),
            'pager'           => $this->_pilot_model->pager,
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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_pilot_model->save($pilot);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating signup.');
        }

        return redirect()->to(url_to('admin.pilots.list'))->with('success', 'Pilot program signup added successfully!');
    }

    /**
     * Renders form to edit an existing pilot signup record.
     *
     * @param string $id
     * @return string|RedirectResponse
     */
    public function pilotEdit(string $id)
    {
        /** @var PilotSignup|null $pilot */
        $pilot = $this->_pilot_model->find((int) $id);

        if (!$pilot) {
            return redirect()->to(url_to('admin.pilots.list'))->with('error', 'Requested pilot signup record not found.');
        }

        $data = [
            'pageTitle'       => 'Edit Pilot Signup | ClearBay',
            'metaDescription' => 'Modify an existing pilot signup application.',
            'canonicalUrl'    => url_to('admin.pilots.edit', $id),
            'robotsTag'       => 'noindex, nofollow',
            'pilot'           => $pilot,
        ];

        return view('App\Modules\Admin\Views\pilots\edit', $data);
    }

    /**
     * Validates and updates an existing pilot signup record.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function pilotUpdate(string $id): RedirectResponse
    {
        /** @var PilotSignup|null $pilot */
        $pilot = $this->_pilot_model->find((int) $id);

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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_pilot_model->save($pilot);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating signup.');
        }

        return redirect()->to(url_to('admin.pilots.list'))->with('success', 'Pilot program signup updated successfully!');
    }

    /**
     * Deletes a pilot signup record.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function pilotDelete(string $id): RedirectResponse
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->_pilot_model->delete((int) $id);
        $db->transComplete();

        if ($db->transStatus() === false) {
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
        $data = [
            'pageTitle'       => 'Manage Handovers | ClearBay',
            'metaDescription' => 'Review and manage ambulance queue handovers.',
            'canonicalUrl'    => url_to('admin.handovers.list'),
            'robotsTag'       => 'noindex, nofollow',
            'handovers'       => $this->_handover_model
                ->select('handovers.*, ambulances.unit_id as ambulance_unit, hospitals.name as hospital_name')
                ->join('ambulances', 'ambulances.id = handovers.ambulance_id')
                ->join('hospitals', 'hospitals.id = handovers.hospital_id')
                ->orderBy('handovers.created_at', 'DESC')
                ->paginate(15, 'handovers'),
            'pager'           => $this->_handover_model->pager,
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
            'hospitals'       => $this->_hospital_model->orderBy('name', 'ASC')->findAll(),
            'ambulances'      => $this->_ambulance_model->orderBy('unit_id', 'ASC')->findAll(),
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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_handover_model->save($handover);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating handover.');
        }

        return redirect()->to(url_to('admin.handovers.list'))->with('success', 'Handover added successfully!');
    }

    /**
     * Renders form to edit an existing handover record.
     *
     * @param string $id
     * @return string|RedirectResponse
     */
    public function handoverEdit(string $id)
    {
        /** @var Handover|null $handover */
        $handover = $this->_handover_model->find((int) $id);

        if (!$handover) {
            return redirect()->to(url_to('admin.handovers.list'))->with('error', 'Requested handover record not found.');
        }

        $data = [
            'pageTitle'       => 'Edit Handover | ClearBay',
            'metaDescription' => 'Modify an existing queue handover.',
            'canonicalUrl'    => url_to('admin.handovers.edit', $id),
            'robotsTag'       => 'noindex, nofollow',
            'handover'        => $handover,
            'hospitals'       => $this->_hospital_model->orderBy('name', 'ASC')->findAll(),
            'ambulances'      => $this->_ambulance_model->orderBy('unit_id', 'ASC')->findAll(),
        ];

        return view('App\Modules\Admin\Views\handovers\edit', $data);
    }

    /**
     * Validates and updates an existing handover record.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function handoverUpdate(string $id): RedirectResponse
    {
        /** @var Handover|null $handover */
        $handover = $this->_handover_model->find((int) $id);

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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_handover_model->save($handover);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating handover.');
        }

        return redirect()->to(url_to('admin.handovers.list'))->with('success', 'Handover updated successfully!');
    }

    /**
     * Deletes a handover record.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function handoverDelete(string $id): RedirectResponse
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->_handover_model->delete((int) $id);
        $db->transComplete();

        if ($db->transStatus() === false) {
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
        $data = [
            'pageTitle'       => 'Manage Hospitals | ClearBay',
            'metaDescription' => 'Review and manage partner hospital records.',
            'canonicalUrl'    => url_to('admin.hospitals.list'),
            'robotsTag'       => 'noindex, nofollow',
            'hospitals'       => $this->_hospital_model->orderBy('name', 'ASC')->paginate(15, 'hospitals'),
            'pager'           => $this->_hospital_model->pager,
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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_hospital_model->save($hospital);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating hospital.');
        }

        return redirect()->to(url_to('admin.hospitals.list'))->with('success', 'Hospital facility added successfully!');
    }

    /**
     * Renders form to edit an existing hospital record.
     *
     * @param string $id
     * @return string|RedirectResponse
     */
    public function hospitalEdit(string $id)
    {
        /** @var Hospital|null $hospital */
        $hospital = $this->_hospital_model->find((int) $id);

        if (!$hospital) {
            return redirect()->to(url_to('admin.hospitals.list'))->with('error', 'Requested hospital record not found.');
        }

        $data = [
            'pageTitle'       => 'Edit Hospital | ClearBay',
            'metaDescription' => 'Modify hospital facility configuration and capacity status.',
            'canonicalUrl'    => url_to('admin.hospitals.edit', $id),
            'robotsTag'       => 'noindex, nofollow',
            'hospital'        => $hospital,
        ];

        return view('App\Modules\Admin\Views\hospitals\edit', $data);
    }

    /**
     * Validates and updates an existing hospital record.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function hospitalUpdate(string $id): RedirectResponse
    {
        /** @var Hospital|null $hospital */
        $hospital = $this->_hospital_model->find((int) $id);

        if (!$hospital) {
            return redirect()->to(url_to('admin.hospitals.list'))->with('error', 'Hospital record not found.');
        }

        $rules = [
            'code'     => 'required|min_length[2]|max_length[10]|is_unique[hospitals.code,id,' . $id . ']',
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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_hospital_model->save($hospital);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating hospital.');
        }

        return redirect()->to(url_to('admin.hospitals.list'))->with('success', 'Hospital updated successfully!');
    }

    /**
     * Deletes a hospital record.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function hospitalDelete(string $id): RedirectResponse
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->_hospital_model->delete((int) $id);
        $db->transComplete();

        if ($db->transStatus() === false) {
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
        $data = [
            'pageTitle'       => 'Manage Ambulances | ClearBay',
            'metaDescription' => 'Review and manage ambulance fleet units.',
            'canonicalUrl'    => url_to('admin.ambulances.list'),
            'robotsTag'       => 'noindex, nofollow',
            'ambulances'      => $this->_ambulance_model->orderBy('unit_id', 'ASC')->paginate(15, 'ambulances'),
            'pager'           => $this->_ambulance_model->pager,
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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_ambulance_model->save($ambulance);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating ambulance.');
        }

        return redirect()->to(url_to('admin.ambulances.list'))->with('success', 'Ambulance vehicle registered successfully!');
    }

    /**
     * Renders form to edit an existing ambulance record.
     *
     * @param string $id
     * @return string|RedirectResponse
     */
    public function ambulanceEdit(string $id)
    {
        /** @var Ambulance|null $ambulance */
        $ambulance = $this->_ambulance_model->find((int) $id);

        if (!$ambulance) {
            return redirect()->to(url_to('admin.ambulances.list'))->with('error', 'Requested ambulance record not found.');
        }

        $data = [
            'pageTitle'       => 'Edit Ambulance | ClearBay',
            'metaDescription' => 'Modify vehicle fleet configuration details.',
            'canonicalUrl'    => url_to('admin.ambulances.edit', $id),
            'robotsTag'       => 'noindex, nofollow',
            'ambulance'       => $ambulance,
        ];

        return view('App\Modules\Admin\Views\ambulances\edit', $data);
    }

    /**
     * Validates and updates an existing ambulance record.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function ambulanceUpdate(string $id): RedirectResponse
    {
        /** @var Ambulance|null $ambulance */
        $ambulance = $this->_ambulance_model->find((int) $id);

        if (!$ambulance) {
            return redirect()->to(url_to('admin.ambulances.list'))->with('error', 'Ambulance record not found.');
        }

        $rules = [
            'unitId'   => 'required|min_length[3]|max_length[50]|is_unique[ambulances.unit_id,id,' . $id . ']',
            'provider' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ambulance->unit_id  = strtoupper((string) $this->request->getPost('unitId'));
        $ambulance->provider = (string) $this->request->getPost('provider');

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_ambulance_model->save($ambulance);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating ambulance.');
        }

        return redirect()->to(url_to('admin.ambulances.list'))->with('success', 'Ambulance configuration updated successfully!');
    }

    /**
     * Deletes an ambulance record.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function ambulanceDelete(string $id): RedirectResponse
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->_ambulance_model->delete((int) $id);
        $db->transComplete();

        if ($db->transStatus() === false) {
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
        $data = [
            'pageTitle'       => 'Manage Users | ClearBay',
            'metaDescription' => 'Review and manage ClearBay operator and staff user accounts.',
            'canonicalUrl'    => url_to('admin.users.list'),
            'robotsTag'       => 'noindex, nofollow',
            'users'           => $this->_user_model
                ->select('users.*, hospitals.name as hospital_name, ems_providers.name as ems_name')
                ->join('hospitals', 'hospitals.id = users.hospital_id', 'left')
                ->join('ems_providers', 'ems_providers.id = users.ems_provider_id', 'left')
                ->orderBy('users.created_at', 'DESC')
                ->paginate(15, 'users'),
            'pager'           => $this->_user_model->pager,
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
            'hospitals'       => $this->_hospital_model->orderBy('name', 'ASC')->findAll(),
            'ems_providers'   => $this->_pilot_model->db->table('ems_providers')->orderBy('name', 'ASC')->get()->getResultArray(),
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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_user_model->save($user);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while creating user.');
        }

        return redirect()->to(url_to('admin.users.list'))->with('success', 'User account registered successfully with temporary password "12345678"!');
    }

    /**
     * Renders form to edit an existing user account.
     *
     * @param string $id
     * @return string|RedirectResponse
     */
    public function userEdit(string $id)
    {
        /** @var User|null $user */
        $user = $this->_user_model->find((int) $id);

        if (!$user) {
            return redirect()->to(url_to('admin.users.list'))->with('error', 'Requested user account not found.');
        }

        $data = [
            'pageTitle'       => 'Edit User Account | ClearBay',
            'metaDescription' => 'Modify account credentials, role levels, and active states.',
            'canonicalUrl'    => url_to('admin.users.edit', $id),
            'robotsTag'       => 'noindex, nofollow',
            'user'            => $user,
            'hospitals'       => $this->_hospital_model->orderBy('name', 'ASC')->findAll(),
            'ems_providers'   => $this->_pilot_model->db->table('ems_providers')->orderBy('name', 'ASC')->get()->getResultArray(),
        ];

        return view('App\Modules\Admin\Views\users\edit', $data);
    }

    /**
     * Validates and updates an existing user account.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function userUpdate(string $id): RedirectResponse
    {
        /** @var User|null $user */
        $user = $this->_user_model->find((int) $id);

        if (!$user) {
            return redirect()->to(url_to('admin.users.list'))->with('error', 'User account not found.');
        }

        $rules = [
            'name'            => 'required|min_length[3]|max_length[255]',
            'email'           => 'required|valid_email|max_length[255]|is_unique[users.email,id,' . $id . ']',
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

        $db = \Config\Database::connect();
        $db->transStart();
        $this->_user_model->save($user);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Database transaction failed while updating user.');
        }

        return redirect()->to(url_to('admin.users.list'))->with('success', 'User account updated successfully!');
    }

    /**
     * Deactivates or deletes a user account.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function userDelete(string $id): RedirectResponse
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->_user_model->delete((int) $id);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(url_to('admin.users.list'))->with('error', 'Database transaction failed while deleting user.');
        }

        return redirect()->to(url_to('admin.users.list'))->with('success', 'User account deleted successfully.');
    }
}
