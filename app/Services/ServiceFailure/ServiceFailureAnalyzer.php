<?php

namespace App\Services\ServiceFailure;

use App\Models\ServiceFailurePattern;
use App\Models\TestResult;
use Illuminate\Support\Collection;

class ServiceFailureAnalyzer
{
    /**
     * Find potential service failures based on failed test flows
     *
     * @param  array  $failedFlowNumbers  Array of flow numbers that failed
     * @param  bool  $hasHttpDevice  Whether an HTTP device is present
     * @return Collection Collection of ServiceFailurePattern that match the failure pattern
     */
    public function analyzePotentialFailures(array $failedFlowNumbers, bool $hasHttpDevice = false): Collection
    {
        return ServiceFailurePattern::whereHas('flows')
            ->get()
            ->filter(function ($pattern) use ($failedFlowNumbers, $hasHttpDevice) {
                return $pattern->matchesFailedFlows($failedFlowNumbers, $hasHttpDevice);
            });
    }

    /**
     * Analyze test results to find failed flows
     *
     * @param  Collection  $testResults  Collection of TestResult models
     * @return array Array of flow numbers that failed
     */
    public function getFailedFlowsFromResults(Collection $testResults): array
    {
        return $testResults
            ->filter(function ($result) {
                return ! $result->success;
            })
            ->map(function ($result) {
                return $this->getFlowNumberFromTestScenario($result->test_scenario);
            })
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get the flow number based on the test scenario type
     *
     * @param  TestScenario  $scenario
     * @return int|null Flow number (1-9) or null if not recognized
     */
    protected function getFlowNumberFromTestScenario($scenario): ?int
    {
        // Map test scenarios to flow numbers based on your existing test types
        $flowMap = [
            'full_route_1' => 1,
            'one_way_route' => 2,
            'two_way_route' => 3,
            'direct_test_1' => 4,
            'direct_test_2' => 5,
            'tb_mqtt_health' => 6,
            'cs_mqtt_health' => 7,
            'tb_http_health' => 8,
            'cs_http_health' => 9,
        ];

        return $flowMap[$scenario->type] ?? null;
    }

    /**
     * Get a human-readable analysis of potential service failures
     *
     * @param  array  $failedFlowNumbers  Array of flow numbers that failed
     * @param  bool  $hasHttpDevice  Whether an HTTP device is present
     * @return string Human-readable analysis
     */
    public function getFailureAnalysis(array $failedFlowNumbers, bool $hasHttpDevice = false): string
    {
        $matchingPatterns = $this->analyzePotentialFailures($failedFlowNumbers, $hasHttpDevice);

        if ($matchingPatterns->isEmpty()) {
            if ($this->isHttpOnlyFailure($failedFlowNumbers)) {
                return 'Only HTTP-related flows are failing. This might indicate issues with the HTTP device configuration.';
            }

            return 'No known service failure pattern matches the current failures. This might indicate multiple service issues or an unknown problem.';
        }

        // With exact matching, we should only ever get one pattern
        $pattern = $matchingPatterns->first();

        return "The failure pattern matches issues typically seen when {$pattern->service_name} is down.";
    }

    /**
     * Check if the failures indicate HTTP-only issues
     *
     * @param  array  $failedFlowNumbers  Array of flow numbers that failed
     * @return bool True if only HTTP-related flows failed
     */
    public function isHttpOnlyFailure(array $failedFlowNumbers): bool
    {
        $nonHttpFlows = range(1, 7);
        $httpFlows = [8, 9];

        // Check if there are any non-HTTP flow failures
        $hasNonHttpFailures = ! empty(array_intersect($failedFlowNumbers, $nonHttpFlows));

        // Check if there are any HTTP flow failures
        $hasHttpFailures = ! empty(array_intersect($failedFlowNumbers, $httpFlows));

        // Return true only if we have HTTP failures but no non-HTTP failures
        return $hasHttpFailures && ! $hasNonHttpFailures;
    }
}
