<?php

namespace Tests\Unit\Models;

use App\Enums\ServiceType;
use App\Enums\StatusType;
use App\Models\TestResult;
use App\Models\TestScenario;
use App\Models\TestScenarioServiceStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TestScenarioServiceStatusTest extends TestCase
{
    use RefreshDatabase;

    private TestScenario $scenario;
    private TestScenarioServiceStatus $status;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable telescope for tests
        config(['telescope.enabled' => false]);

        // Create a test scenario
        $this->scenario = TestScenario::factory()->create();
        
        // Create a service status
        $this->status = TestScenarioServiceStatus::getOrCreateForService(
            $this->scenario,
            ServiceType::THINGSBOARD
        );
    }

    #[Test]
    public function it_starts_with_healthy_status(): void
    {
        $this->assertEquals(StatusType::HEALTHY, $this->status->status);
        $this->assertEquals(100, $this->status->success_rate_1h);
        $this->assertTrue($this->status->isHealthy());
    }

    #[Test]
    public function it_becomes_warning_when_success_rate_drops_below_90(): void
    {
        // Simulate 8 successes and 2 failures = 80% success rate
        for ($i = 0; $i < 8; $i++) {
            $this->status->updateStatus(true);
        }
        for ($i = 0; $i < 2; $i++) {
            $this->status->updateStatus(false);
        }

        $this->assertEquals(StatusType::WARNING, $this->status->status);
        $this->assertEquals(80, $this->status->success_rate_1h);
        $this->assertTrue($this->status->isWarning());
    }

    #[Test]
    public function it_becomes_critical_after_10_minutes_of_downtime(): void
    {
        // Set initial failure
        $this->status->updateStatus(false);
        
        // Move time forward 11 minutes
        $this->travel(11)->minutes();
        
        // Another failure after 11 minutes
        $this->status->updateStatus(false);

        $this->assertEquals(StatusType::CRITICAL, $this->status->status);
        $this->assertTrue($this->status->isCritical());
        $this->assertGreaterThanOrEqual(10, $this->status->getCurrentDowntime());
    }

    #[Test]
    public function it_recovers_from_critical_to_healthy_after_success(): void
    {
        // Make it critical first
        $this->status->updateStatus(false);
        $this->travel(11)->minutes();
        $this->status->updateStatus(false);
        
        // Verify it's critical
        $this->assertTrue($this->status->isCritical());

        // Simulate recovery
        $this->status->updateStatus(true);

        $this->assertEquals(StatusType::HEALTHY, $this->status->status);
        $this->assertTrue($this->status->isHealthy());
        $this->assertEquals(0, $this->status->getCurrentDowntime());
    }

    #[Test]
    public function it_handles_test_results_correctly(): void
    {
        // Create a failing test result
        $result = TestResult::factory()->create([
            'test_scenario_id' => $this->scenario->id,
            'service_type' => ServiceType::THINGSBOARD,
            'status' => TestResult::STATUS_FAILURE,
        ]);

        // Handle the result
        TestScenarioServiceStatus::handleTestResult($result);

        // Refresh the status
        $this->status->refresh();

        // Should have recorded the failure
        $this->assertEquals(0, $this->status->success_count_1h);
        $this->assertEquals(1, $this->status->total_count_1h);
        $this->assertEquals(0, $this->status->success_rate_1h);
        $this->assertNotNull($this->status->downtime_started_at);
    }

    #[Test]
    public function it_resets_counters_after_one_hour(): void
    {
        // Add some results
        $this->status->updateStatus(true);
        $this->status->updateStatus(false);
        
        // Move time forward 61 minutes
        $this->travel(61)->minutes();
        
        // Add new result
        $this->status->updateStatus(true);

        // Counters should have reset
        $this->assertEquals(1, $this->status->success_count_1h);
        $this->assertEquals(1, $this->status->total_count_1h);
        $this->assertEquals(100, $this->status->success_rate_1h);
    }
}
