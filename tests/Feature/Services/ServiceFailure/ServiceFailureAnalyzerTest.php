<?php

namespace Tests\Feature\Services\ServiceFailure;

use App\Models\ServiceFailurePattern;
use App\Services\ServiceFailure\ServiceFailureAnalyzer;
use Database\Seeders\ServiceFailurePatternsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceFailureAnalyzerTest extends TestCase
{
    use DatabaseMigrations;

    protected ServiceFailureAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed our matrix data
        $this->seed(ServiceFailurePatternsSeeder::class);

        // Debug: Check if patterns were seeded
        dump('Number of patterns:', ServiceFailurePattern::count());
        dump('First pattern flows:', ServiceFailurePattern::first()?->flows()->count());

        $this->analyzer = new ServiceFailureAnalyzer;
    }

    /** @test */
    public function test_it_identifies_thingsboard_failure_without_http_device()
    {
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 4, 5, 6], false);
        $this->assertStringContainsString('ThingsBoard', $analysis);
    }

    /** @test */
    public function test_it_identifies_thingsboard_failure_with_http_device()
    {
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 4, 5, 6, 8], true);
        $this->assertStringContainsString('ThingsBoard', $analysis);
    }

    /** @test */
    public function test_it_rejects_thingsboard_failure_with_wrong_http_flows()
    {
        // Flow 9 should not fail for ThingsBoard, even with HTTP device
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 4, 5, 6, 8, 9], true);
        $this->assertStringNotContainsString('ThingsBoard', $analysis);
        $this->assertStringContainsString('No known service failure pattern', $analysis);
    }

    /** @test */
    public function test_it_identifies_chirpstack_failure_without_http_device()
    {
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 4, 5, 7], false);
        $this->assertStringContainsString('ChirpStack', $analysis);
    }

    /** @test */
    public function test_it_identifies_chirpstack_failure_with_http_device()
    {
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 4, 5, 7, 9], true);
        $this->assertStringContainsString('ChirpStack', $analysis);
    }

    /** @test */
    public function test_it_rejects_chirpstack_failure_with_wrong_http_flows()
    {
        // Flow 8 should not fail for ChirpStack
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 4, 5, 7, 8], true);
        $this->assertStringNotContainsString('ChirpStack', $analysis);
        $this->assertStringContainsString('No known service failure pattern', $analysis);
    }

    /** @test */
    public function test_it_identifies_mqtt_broker_failure_without_http_device()
    {
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 6, 7], false);
        $this->assertStringContainsString('MQTT Broker', $analysis);
    }

    /** @test */
    public function test_it_identifies_mqtt_broker_failure_with_http_device()
    {
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 6, 7], true);
        $this->assertStringContainsString('MQTT Broker', $analysis);
    }

    /** @test */
    public function test_it_identifies_lora_tx_failure_without_http_device()
    {
        $analysis = $this->analyzer->getFailureAnalysis([1, 3], false);
        $this->assertStringContainsString('LoRa TX', $analysis);
    }

    /** @test */
    public function test_it_identifies_lora_rx_failure_without_http_device()
    {
        $analysis = $this->analyzer->getFailureAnalysis([2, 4, 5], false);
        $this->assertStringContainsString('LoRa RX', $analysis);
    }

    /** @test */
    public function test_it_handles_unknown_failure_pattern()
    {
        $analysis = $this->analyzer->getFailureAnalysis([1, 4, 7], false);
        $this->assertStringContainsString('No known service failure pattern', $analysis);
    }

    /** @test */
    public function test_it_identifies_http_only_failures()
    {
        $this->assertTrue($this->analyzer->isHttpOnlyFailure([8, 9]));
        $this->assertFalse($this->analyzer->isHttpOnlyFailure([1, 8, 9]));
    }

    /** @test */
    public function test_it_provides_human_readable_analysis()
    {
        // Single service without HTTP device
        $analysis = $this->analyzer->getFailureAnalysis([1, 3], false);
        $this->assertStringContainsString('LoRa TX', $analysis);

        // Single service with HTTP device
        $analysis = $this->analyzer->getFailureAnalysis([1, 2, 3, 4, 5, 6, 8], true);
        $this->assertStringContainsString('ThingsBoard', $analysis);

        // Unknown pattern
        $analysis = $this->analyzer->getFailureAnalysis([1, 4, 7], false);
        $this->assertStringContainsString('No known service failure pattern', $analysis);
    }
}
