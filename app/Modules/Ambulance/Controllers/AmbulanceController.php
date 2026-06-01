<?php

declare(strict_types=1);

namespace App\Modules\Ambulance\Controllers;

use App\Controllers\BaseController;
use App\Modules\Ambulance\Libraries\AmbulanceService;
use App\Modules\Ambulance\Entities\Ambulance;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Class AmbulanceController
 *
 * Coordinates mobile-responsive screens and API endpoints for paramedics en route.
 */
class AmbulanceController extends BaseController
{
    /**
     * @var AmbulanceService
     */
    private AmbulanceService $ambulance_service;

    /**
     * AmbulanceController constructor.
     */
    public function __construct()
    {
        $this->ambulance_service = new AmbulanceService();
        helper(['form', 'url']);
    }

    // --- Helper Methods ---

    /**
     * Helper to retrieve active ambulance mapping for currently authenticated paramedic.
     *
     * @return Ambulance|null
     */
    private function _getActiveAmbulance(): ?Ambulance
    {
        $user_id = session()->get('user_id');
        if ($user_id === null) {
            return null;
        }

        return $this->ambulance_service->getActiveAmbulance((int) $user_id);
    }

    /**
     * Calculates simple distance using Haversine formula.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    private function _haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth_radius = 6371; // Kilometers

        $d_lat = deg2rad($lat2 - $lat1);
        $d_lon = deg2rad($lon2 - $lon1);

        $a = sin($d_lat / 2) * sin($d_lat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($d_lon / 2) * sin($d_lon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earth_radius * $c, 1);
    }

    /**
     * Renders the mobile-responsive home dashboard (SC-07).
     *
     * @return ResponseInterface|string
     */
    public function home(): string|RedirectResponse
    {
        $ambulance = $this->_getActiveAmbulance();
        if ($ambulance === null) {
            return redirect()->to(url_to('auth.login'))->with('error', 'Session invalid or vehicle mapping missing.');
        }

        // Fetch hospitals
        $hospitals = $this->ambulance_service->getHospitals();

        // Sort by distance (Haversine)
        $hosp_list = [];
        $my_lat = $ambulance->current_lat ?? -1.2921; // Nairobi default
        $my_lng = $ambulance->current_lng ?? 36.8219;

        foreach ($hospitals as $h) {
            $h_lat = (float) $h->lat;
            $h_lng = (float) $h->lng;
            $dist  = $this->_haversineDistance((float) $my_lat, (float) $my_lng, $h_lat, $h_lng);

            $hosp_list[] = [
                'hospital' => $h,
                'distance' => $dist,
                'eta'      => (int) round($dist * 2.5 + 2), // Rough traffic ETA calculation
            ];
        }

        // Sort by distance
        usort($hosp_list, static function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        $data = [
            'page_title'       => 'Ambulance Navigator | ClearBay',
            'meta_description' => 'Live Nairobi emergency department capacity mapping for paramedics.',
            'canonical_url'    => url_to('ambulance.home'),
            'robots_tag'       => 'noindex, nofollow',
            'ambulance'        => $ambulance,
            'hospitals'        => $hosp_list,
            'mapbox_token'     => env('mapboxgl.accessToken'),
        ];

        return view('App\Modules\Ambulance\Views\home', $data);
    }

    /**
     * Renders Hospital Capacity Details View (SC-08).
     *
     * @param string $id
     * @return ResponseInterface|string
     */
    public function detail(string $id): string|RedirectResponse
    {
        $hospital_id = (int) $id;
        $details = $this->ambulance_service->getHospitalDetails($hospital_id);

        if (empty($details)) {
            return redirect()->to(url_to('ambulance.home'))->with('error', 'Hospital not found.');
        }

        $data = [
            'page_title'       => $details['hospital']->name . ' Capacity | ClearBay',
            'meta_description' => 'Available bays, queue length, and off-load wait times.',
            'canonical_url'    => url_to('ambulance.hospital.detail', $hospital_id),
            'robots_tag'       => 'noindex, nofollow',
            'details'          => $details,
        ];

        return view('App\Modules\Ambulance\Views\detail', $data);
    }

    /**
     * Renders Pre-Notification Form (SC-09).
     *
     * @param string $id
     * @return ResponseInterface|string
     */
    public function preNotifyForm(string $id): string|RedirectResponse
    {
        $hospital_id = (int) $id;
        $details = $this->ambulance_service->getHospitalDetails($hospital_id);

        if (empty($details)) {
            return redirect()->to(url_to('ambulance.home'))->with('error', 'Hospital not found.');
        }

        if ($details['hospital']->status === 'RED') {
            return redirect()->back()->with('error', 'Facility is full. Please select another.');
        }

        $ambulance = $this->_getActiveAmbulance();
        $my_lat = $ambulance->current_lat ?? -1.2921;
        $my_lng = $ambulance->current_lng ?? 36.8219;
        $dist  = $this->_haversineDistance((float) $my_lat, (float) $my_lng, (float) $details['hospital']->lat, (float) $details['hospital']->lng);
        $eta   = (int) round($dist * 2.5 + 2);

        $data = [
            'page_title'       => 'Pre-Notify ED | ClearBay',
            'meta_description' => 'Send pre-arrival patient characteristics to emergency department.',
            'canonical_url'    => url_to('ambulance.pre_notify', $hospital_id),
            'robots_tag'       => 'noindex, nofollow',
            'hospital'         => $details['hospital'],
            'eta'              => $eta,
        ];

        return view('App\Modules\Ambulance\Views\pre_notify', $data);
    }

    /**
     * Submits a pre-notification en route.
     *
     * @return ResponseInterface
     */
    public function sendPreNotification(): ResponseInterface
    {
        $rules = [
            'hospital_id'     => 'required|integer',
            'patient_age'     => 'required|integer|greater_than_equal_to[0]',
            'patient_sex'     => 'required|in_list[Male,Female,Not Specified]',
            'chief_complaint' => 'required|string|max_length[100]',
            'acuity'          => 'required|in_list[Critical,Serious,Stable]',
            'notes'           => 'permit_empty|max_length[150]',
            'eta_minutes'     => 'required|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Form validation failed.',
                'errors'     => $this->validator->getErrors(),
                'csrf_token' => csrf_hash()
            ]);
        }

        $paramedic_id = session()->get('user_id');
        if ($paramedic_id === null) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Session expired.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $hospital_id     = (int) $this->request->getPost('hospital_id');
        $patient_age     = (int) $this->request->getPost('patient_age');
        $patient_sex     = (string) $this->request->getPost('patient_sex');
        $chief_complaint = (string) $this->request->getPost('chief_complaint');
        $acuity          = (string) $this->request->getPost('acuity');
        $notes           = (string) ($this->request->getPost('notes') ?? '');
        $eta_minutes     = (int) $this->request->getPost('eta_minutes');

        $pre_id = $this->ambulance_service->sendPreNotification(
            (int) $paramedic_id,
            $hospital_id,
            $patient_age,
            $patient_sex,
            $chief_complaint,
            $acuity,
            $notes,
            $eta_minutes
        );

        if ($pre_id === null) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Database error dispatching pre-alert.',
                'csrf_token' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'      => 'success',
            'message'     => 'Pre-Notification sent successfully.',
            'redirect_to' => url_to('ambulance.active_run', $pre_id),
            'csrf_token'  => csrf_hash()
        ]);
    }

    /**
     * Renders active countdown en-route view (SC-11).
     *
     * @param string $id
     * @return ResponseInterface|string
     */
    public function activeRun(string $id): ResponseInterface|string
    {
        $pre_id = (int) $id;
        $status = $this->ambulance_service->getActiveRunStatus($pre_id);

        if (empty($status)) {
            if ($this->request->getGet('ajax') === '1' || $this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'     => 'error',
                    'message'    => 'Active run record not found.',
                    'csrf_token' => csrf_hash()
                ]);
            }
            return redirect()->to(url_to('ambulance.home'))->with('error', 'Active run record not found.');
        }

        if ($this->request->getGet('ajax') === '1' || $this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'     => 'success',
                'result'     => $status,
                'csrf_token' => csrf_hash()
            ]);
        }

        $data = [
            'page_title'       => 'Active Run en Route | ClearBay',
            'meta_description' => 'En route telemetry tracking and clinician bay readiness countdown.',
            'canonical_url'    => url_to('ambulance.active_run', $pre_id),
            'robots_tag'       => 'noindex, nofollow',
            'pre_id'           => $pre_id,
            'status'           => $status,
        ];

        return view('App\Modules\Ambulance\Views\active_run', $data);
    }

    /**
     * REST Endpoint updating paramedic's current coordinates every 30s with CSRF rotation.
     *
     * @return ResponseInterface
     */
    public function updateLocation(): ResponseInterface
    {
        $rules = [
            'lat' => 'required|decimal',
            'lng' => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Invalid coordinate data.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $ambulance = $this->_getActiveAmbulance();
        if ($ambulance === null) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Vehicle mapping missing.',
                'csrf_token' => csrf_hash()
            ]);
        }

        $lat = (float) $this->request->getPost('lat');
        $lng = (float) $this->request->getPost('lng');

        $success = $this->ambulance_service->updateLocation((int) $ambulance->id, $lat, $lng);

        if (!$success) {
            return $this->response->setJSON([
                'status'     => 'error',
                'message'    => 'Failed to save location updates.',
                'csrf_token' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'Ambulance location coordinates synchronized.',
            'csrf_token' => csrf_hash()
        ]);
    }
}
