<?php

declare(strict_types=1);

namespace App\Modules\Ambulance\Libraries;

use App\Modules\Ambulance\Models\AmbulanceModel;
use App\Modules\Ambulance\Entities\Ambulance;
use App\Modules\Hospital\Models\HospitalModel;
use App\Modules\Hospital\Models\PreNotificationModel;
use App\Modules\Hospital\Models\HandoverModel;
use App\Modules\Hospital\Entities\PreNotification;
use App\Modules\Hospital\Entities\Handover;
use App\Modules\Auth\Models\UserModel;
use App\Modules\Auth\Entities\User;

/**
 * Class AmbulanceService
 *
 * Coordinates ambulance GPS telemetry, hospital lists, and pre-notifications.
 */
class AmbulanceService
{
    /** Default Nairobi center coordinates for server-side fallback when ambulance has no stored GPS. */
    public const NAIROBI_LAT = -1.2921;
    public const NAIROBI_LNG = 36.8219;

    /**
     * @var AmbulanceModel
     */
    private AmbulanceModel $ambulance_model;

    /**
     * @var HospitalModel
     */
    private HospitalModel $hospital_model;

    /**
     * @var PreNotificationModel
     */
    private PreNotificationModel $pre_model;

    /**
     * @var HandoverModel
     */
    private HandoverModel $handover_model;

    /**
     * @var UserModel
     */
    private UserModel $user_model;

    /**
     * AmbulanceService constructor.
     */
    public function __construct()
    {
        $this->ambulance_model = new AmbulanceModel();
        $this->hospital_model  = new HospitalModel();
        $this->pre_model       = new PreNotificationModel();
        $this->handover_model  = new HandoverModel();
        $this->user_model      = new UserModel();
    }

    // --- Helper Methods ---

    /**
     * Calls the Mapbox Directions Matrix API for a single origin → destination pair.
     *
     * @param float $originLat  Source latitude.
     * @param float $originLng  Source longitude.
     * @param float $destLat    Destination latitude.
     * @param float $destLng    Destination longitude.
     * @return int|null Duration in minutes, or null on API failure.
     */
    private function _fetchMatrixEta(float $originLat, float $originLng, float $destLat, float $destLng): ?int
    {
        $token = env('mapboxgl.accessToken');
        if (empty($token)) {
            return null;
        }

        $coords = "{$originLng},{$originLat};{$destLng},{$destLat}";
        $url = "https://api.mapbox.com/directions-matrix/v1/mapbox/driving/{$coords}" .
            "?sources=0&annotations=duration&access_token={$token}";

        try {
            $response = service('curlrequest')->get($url);
            $data = json_decode($response->getBody(), true);
            if (($data['code'] ?? '') !== 'Ok' || !isset($data['durations'][0][1])) {
                return null;
            }
            return (int) round($data['durations'][0][1] / 60); // seconds → minutes
        } catch (\Throwable $e) {
            log_message('error', 'Mapbox Matrix API call failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetches driving distances and ETAs from Mapbox Matrix API for all hospitals in a single batch.
     *
     * @param float $sourceLat  Paramedic current latitude.
     * @param float $sourceLng  Paramedic current longitude.
     * @param array $hospitals  Array of Hospital entities with ->lat, ->lng properties.
     * @return array Array of ['hospital' => Entity, 'distance' => float, 'eta' => int], sorted by distance.
     */
    public function getMatrixEtas(float $sourceLat, float $sourceLng, array $hospitals): array
    {
        $token = env('mapboxgl.accessToken');
        $results = [];
        $use_fallback = true;

        if (!empty($token)) {
            // Build coordinate string: source + all hospitals
            $coords = "{$sourceLng},{$sourceLat}";
            $index_map = [];
            foreach ($hospitals as $i => $h) {
                $coords .= ";{$h->lng},{$h->lat}";
                $index_map[$i] = $h;
            }

            $url = "https://api.mapbox.com/directions-matrix/v1/mapbox/driving/{$coords}" .
                "?sources=0&annotations=distance,duration&access_token={$token}";

            try {
                $response = service('curlrequest')->get($url);
                $data = json_decode($response->getBody(), true);

                if (($data['code'] ?? '') === 'Ok' && isset($data['durations'][0])) {
                    $use_fallback = false;
                    $distance_row = $data['distances'][0] ?? null;
                    $duration_row = $data['durations'][0];

                    foreach ($hospitals as $i => $h) {
                        $raw_distance = $distance_row[$i] ?? null;
                        $raw_duration = $duration_row[$i] ?? null;

                        if ($raw_distance !== null && $raw_duration !== null) {
                            $results[] = [
                                'hospital' => $h,
                                'distance' => round($raw_distance / 1000, 1), // meters → km
                                'eta'      => (int) round($raw_duration / 60), // seconds → min
                            ];
                        } else {
                            // Matrix returned null for this pair — indicate unknown
                            $results[] = [
                                'hospital' => $h,
                                'distance' => 0,
                                'eta'      => 0,
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'Mapbox Matrix batch call failed: ' . $e->getMessage());
            }
        }

        // If Mapbox batch failed entirely, return unsorted list with zeroed ETAs
        if ($use_fallback && empty($results)) {
            foreach ($hospitals as $h) {
                $results[] = [
                    'hospital' => $h,
                    'distance' => 0,
                    'eta'      => 0,
                ];
            }
        }

        // Sort by distance ascending
        usort($results, static function (array $a, array $b): int {
            return $a['distance'] <=> $b['distance'];
        });

        return $results;
    }

    /**
     * Fetches single-pair ETA from Mapbox Matrix API exclusively.
     *
     * @param float $lat1 Starting latitude.
     * @param float $lon1 Starting longitude.
     * @param float $lat2 Destination latitude.
     * @param float $lon2 Destination longitude.
     * @return int Estimated minutes, or 0 if Mapbox unavailable.
     */
    public function fetchSingleEta(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $matrix = $this->_fetchMatrixEta($lat1, $lon1, $lat2, $lon2);
        return $matrix ?? 0;
    }

    /**
     * Resolves active ambulance entity based on paramedic user identification.
     *
     * @param int $user_id
     * @return Ambulance|null
     */
    public function getActiveAmbulance(int $user_id): ?Ambulance
    {
        /** @var User|null $user */
        $user = $this->user_model->find($user_id);
        if ($user === null || $user->ems_provider_id === null) {
            return null;
        }

        /** @var Ambulance|null $ambulance */
        $ambulance = $this->ambulance_model->where('ems_provider_id', $user->ems_provider_id)->first();
        return $ambulance;
    }

    /**
     * Checks whether the given ambulance has any un-Cleared handover record.
     *
     * @param int $ambulance_id
     * @return bool
     */
    public function hasActiveRun(int $ambulance_id): bool
    {
        return $this->handover_model
            ->where('ambulance_id', $ambulance_id)
            ->where('status !=', 'Cleared')
            ->countAllResults() > 0;
    }

    /**
     * Returns the active run pre-notification ID for an ambulance, or null.
     *
     * @param int $ambulance_id
     * @return int|null
     */
    public function getActiveRunId(int $ambulance_id): ?int
    {
        /** @var Handover|null $handover */
        $handover = $this->handover_model
            ->select('pre_notification_id')
            ->where('ambulance_id', $ambulance_id)
            ->where('status !=', 'Cleared')
            ->first();

        if ($handover === null || $handover->pre_notification_id === null || (int) $handover->pre_notification_id === 0) {
            return null;
        }

        return (int) $handover->pre_notification_id;
    }

    /**
     * Retrieves the active handover and its destination hospital for the given ambulance.
     *
     * @param int $ambulance_id
     * @return array{handover: Handover, hospital: \App\Modules\Hospital\Entities\Hospital}|null
     */
    public function getActiveHandoverWithHospital(int $ambulance_id): ?array
    {
        /** @var Handover|null $handover */
        $handover = $this->handover_model
            ->where('ambulance_id', $ambulance_id)
            ->where('status !=', 'Cleared')
            ->first();

        if ($handover === null) {
            return null;
        }

        /** @var \App\Modules\Hospital\Entities\Hospital|null $hospital */
        $hospital = $this->hospital_model->find($handover->hospital_id);

        if ($hospital === null) {
            return null;
        }

        return ['handover' => $handover, 'hospital' => $hospital];
    }

    /**
     * Updates active ambulance coordinates and recalculates dynamic ETA.
     *
     * @param int   $ambulance_id
     * @param float $lat
     * @param float $lng
     * @param float $hospital_lat Destination hospital latitude for ETA calculation.
     * @param float $hospital_lng Destination hospital longitude for ETA calculation.
     * @return array{success: bool, eta: int|null}
     */
    public function updateLocation(int $ambulance_id, float $lat, float $lng, float $hospital_lat, float $hospital_lng): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Update ambulance coordinates
        $this->ambulance_model->update($ambulance_id, [
            'current_lat'  => $lat,
            'current_lng'  => $lng,
            'last_updated' => date('Y-m-d H:i:s'),
        ]);

        // Calculate dynamic ETA via Mapbox Matrix (exclusive — no Haversine fallback)
        $eta = $this->fetchSingleEta($lat, $lng, $hospital_lat, $hospital_lng);

        // Update pre_notifications.eta_minutes
        /** @var Handover|null $handover */
        $handover = $this->handover_model
            ->where('ambulance_id', $ambulance_id)
            ->where('status !=', 'Cleared')
            ->first();

        if ($handover !== null && $handover->pre_notification_id !== null) {
            $this->pre_model->update($handover->pre_notification_id, [
                'eta_minutes' => $eta,
            ]);

            // Update handovers.eta_minutes
            $this->handover_model->update($handover->id, [
                'eta_minutes' => $eta,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'eta' => null];
        }

        return ['success' => true, 'eta' => $eta];
    }

    /**
     * Updates only the ambulance coordinates (legacy method without ETA recalc).
     *
     * @param int   $ambulance_id
     * @param float $lat
     * @param float $lng
     * @return bool
     */
    public function updateCoordinatesOnly(int $ambulance_id, float $lat, float $lng): bool
    {
        return $this->ambulance_model->update($ambulance_id, [
            'current_lat'  => $lat,
            'current_lng'  => $lng,
            'last_updated' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Retrieves all active hospitals.
     *
     * @return array
     */
    public function getHospitals(): array
    {
        return $this->hospital_model->where('active', 1)->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Retrieves detailed hospital specs and active queue sizes.
     *
     * @param int $hospital_id
     * @return array
     */
    public function getHospitalDetails(int $hospital_id): array
    {
        /** @var \App\Modules\Hospital\Entities\Hospital|null $hospital */
        $hospital = $this->hospital_model->find($hospital_id);
        if ($hospital === null) {
            return [];
        }

        $queue_count = $this->handover_model
            ->where('hospital_id', $hospital_id)
            ->where('status !=', 'Cleared')
            ->countAllResults();

        // Calculate average wait time today (in minutes) for estimation
        $today_start = date('Y-m-d 00:00:00');
        $db = \Config\Database::connect();
        $stats = $db->table('handovers')
            ->select('COUNT(id) as completed_count, SUM(wait_time_minutes) as total_wait')
            ->where('hospital_id', $hospital_id)
            ->where('status', 'Cleared')
            ->where('updated_at >=', $today_start)
            ->get()
            ->getRow();

        $completed_count = (int) ($stats->completed_count ?? 0);
        $total_wait = (int) ($stats->total_wait ?? 0);
        $avg_wait_today = $completed_count > 0 ? (int) round($total_wait / $completed_count) : 8; // default to 8 min

        return [
            'hospital'    => $hospital,
            'queue_count' => $queue_count,
            'avg_wait'    => $avg_wait_today,
        ];
    }

    /**
     * Dispatches a pre-notification en route and creates related queue handover record.
     *
     * @param int    $paramedic_id
     * @param int    $hospital_id
     * @param int    $patient_age
     * @param string $patient_sex
     * @param string $chief_complaint
     * @param string $acuity
     * @param string $notes
     * @param int    $eta_minutes
     * @return int|null
     */
    public function sendPreNotification(
        int $paramedic_id,
        int $hospital_id,
        int $patient_age,
        string $patient_sex,
        string $chief_complaint,
        string $acuity,
        string $notes,
        int $eta_minutes
    ): ?int {
        // Fetch paramedic user
        /** @var User|null $user */
        $user = $this->user_model->find($paramedic_id);
        if ($user === null || $user->ems_provider_id === null) {
            return null;
        }

        // Fetch corresponding ambulance
        /** @var Ambulance|null $ambulance */
        $ambulance = $this->ambulance_model->where('ems_provider_id', $user->ems_provider_id)->first();
        if ($ambulance === null) {
            return null;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $sent_at = date('Y-m-d H:i:s');

        // A. Insert Pre-Notification
        $pre = new PreNotification([
            'ambulance_id'    => $ambulance->id,
            'hospital_id'     => $hospital_id,
            'paramedic_id'    => $paramedic_id,
            'patient_age'     => $patient_age,
            'patient_sex'     => $patient_sex,
            'chief_complaint' => $chief_complaint,
            'acuity'          => $acuity,
            'notes'           => $notes,
            'eta_minutes'     => $eta_minutes,
            'status'          => 'Pending',
            'sent_at'         => $sent_at,
        ]);
        $this->pre_model->save($pre);
        $pre_id = (int) $this->pre_model->getInsertID();

        // B. Insert corresponding Handover row to populate ED queue dashboard
        $handover = new Handover([
            'pre_notification_id' => $pre_id,
            'ambulance_id'        => $ambulance->id,
            'hospital_id'         => $hospital_id,
            'patient_age'         => $patient_age,
            'patient_gender'      => $patient_sex === 'Male' ? 'M' : ($patient_sex === 'Female' ? 'F' : 'M'),
            'acuity'              => $acuity,
            'eta_minutes'         => $eta_minutes,
            'wait_time_minutes'   => 0,
            'status'              => 'En route',
            'arrived_at'          => null,
        ]);
        $this->handover_model->save($handover);

        // C. Update ambulance status
        $this->ambulance_model->update($ambulance->id, [
            'status'       => 'Transporting',
            'last_updated' => $sent_at,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return null;
        }

        return $pre_id;
    }

    /**
     * Checks telemetry en route and hospital sign-off status.
     *
     * @param int $pre_id
     * @return array
     */
    public function getActiveRunStatus(int $pre_id): array
    {
        /** @var PreNotification|null $pre */
        $pre = $this->pre_model->find($pre_id);
        if ($pre === null) {
            return [];
        }

        /** @var \App\Modules\Hospital\Entities\Hospital|null $hospital */
        $hospital = $this->hospital_model->find($pre->hospital_id);

        /** @var Handover|null $handover */
        $handover = $this->handover_model->where('pre_notification_id', $pre_id)->first();

        $status = 'En route';
        if ($handover !== null) {
            $status = $handover->status;
        }

        return [
            'status'            => $status,
            'eta_minutes'       => $pre->eta_minutes,
            'hospital_name'     => $hospital ? $hospital->name : '',
            'hospital_status'   => $hospital ? $hospital->status : 'GREEN',
            'hospital_wait'     => $hospital ? $hospital->status : 'GREEN',
            'bay_preparation'   => ($status === 'Preparing' || $status === 'Arrived' || $status === 'Acknowledged') ? true : false,
        ];
    }
}
