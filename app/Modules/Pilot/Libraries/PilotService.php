<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Libraries;

/**
 * Class PilotService
 */
class PilotService
{
    /**
     * PilotService constructor.
     */
    public function __construct()
    {
        // Initialize dependencies
    }

    /**
     * Saves a pilot signup record wrapped in a database transaction.
     */
    public function registerSignup(\App\Modules\Pilot\Entities\PilotSignup $signup): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $model = new \App\Modules\Pilot\Models\PilotSignupModel();
        $model->save($signup);
        $db->transComplete();
        return $db->transStatus() !== false;
    }
}
