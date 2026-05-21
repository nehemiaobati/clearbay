<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Modules\Admin\Controllers\AdminController;

/**
 * Class AdminControllerTest
 *
 * Verifies that the AdminController routes and actions execute properly.
 *
 * @package Tests\Unit
 */
final class AdminControllerTest extends CIUnitTestCase
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
     * Verifies that the dashboard action renders the administrative control panel.
     */
    public function testDashboardCanBeRendered(): void
    {
        $result = $this->controller(AdminController::class)
                       ->execute('dashboard');

        $this->assertTrue($result->isOK());
        $this->assertStringContainsString('System', $result->getBody());
        $this->assertStringContainsString('Administration', $result->getBody());
    }

    /**
     * Verifies that the pilots list action renders properly.
     */
    public function testPilotsListCanBeRendered(): void
    {
        $result = $this->controller(AdminController::class)
                       ->execute('pilotsList');

        $this->assertTrue($result->isOK());
        $this->assertStringContainsString('Pilot Signups Registry', $result->getBody());
    }

    /**
     * Verifies that the handovers list action renders properly.
     */
    public function testHandoversListCanBeRendered(): void
    {
        $result = $this->controller(AdminController::class)
                       ->execute('handoversList');

        $this->assertTrue($result->isOK());
        $this->assertStringContainsString('Handovers Queue Registry', $result->getBody());
    }

    /**
     * Verifies that the hospitals list action renders properly.
     */
    public function testHospitalsListCanBeRendered(): void
    {
        $result = $this->controller(AdminController::class)
                       ->execute('hospitalsList');

        $this->assertTrue($result->isOK());
        $this->assertStringContainsString('Facilities Registry', $result->getBody());
    }

    /**
     * Verifies that the ambulances list action renders properly.
     */
    public function testAmbulancesListCanBeRendered(): void
    {
        $result = $this->controller(AdminController::class)
                       ->execute('ambulancesList');

        $this->assertTrue($result->isOK());
        $this->assertStringContainsString('Fleet Registry', $result->getBody());
    }
}
