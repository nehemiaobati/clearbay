<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Modules\Pilot\Controllers\PilotController;
use App\Modules\Pilot\Models\PilotSignupModel;

/**
 * Class PilotControllerTest
 *
 * Verifies that the PilotController signup actions work correctly, including validation.
 *
 * @package Tests\Unit
 */
final class PilotControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    use DatabaseTestTrait;

    /**
     * @var bool
     */
    protected $migrate = true;

    /**
     * @var string|null
     */
    protected $namespace = null;

    /**
     * Set up tests, enabling migration/seeding if required.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Verifies that signup fails with invalid inputs.
     */
    public function testSignupFailsWithInvalidInputs(): void
    {
        $result = $this->withBody('')
                       ->controller(PilotController::class)
                       ->execute('signup');

        $this->assertTrue($result->isOK());
        
        $responseBody = $result->getBody();
        $this->assertJson($responseBody);
        
        $data = json_decode($responseBody, true);
        $this->assertEquals('error', $data['status']);
        $this->assertArrayHasKey('errors', $data);
    }

    /**
     * Verifies that signup succeeds with valid inputs and saves to database.
     */
    public function testSignupSucceedsWithValidInputs(): void
    {
        $_POST = [
            'fullName'     => 'Jane Doe',
            'emailAddress' => 'jane.doe@hospital.ke',
            'organisation' => 'Aga Khan University Hospital',
            'userRole'     => 'ED Manager / Charge Nurse',
            'phoneNumber'  => '+254711222333',
            'message'      => 'Looking forward to testing ClearBay!'
        ];

        $result = $this->controller(PilotController::class)
                       ->execute('signup');

        $this->assertTrue($result->isOK());
        
        $responseBody = $result->getBody();
        $this->assertJson($responseBody);
        
        $data = json_decode($responseBody, true);
        $this->assertEquals('success', $data['status']);

        // Check if database contains the record
        $model = new PilotSignupModel();
        /** @var \App\Modules\Pilot\Entities\PilotSignup|null $record */
        $record = $model->where('email_address', 'jane.doe@hospital.ke')->first();

        $this->assertNotNull($record);
        $this->assertEquals('Jane Doe', $record->full_name);
        $this->assertEquals('Aga Khan University Hospital', $record->organisation);
        $this->assertEquals('ED Manager / Charge Nurse', $record->user_role);
    }
}
