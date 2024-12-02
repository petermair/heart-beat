<?php

namespace Database\Factories;

use App\Enums\ServiceType;
use App\Models\TestResult;
use App\Models\TestScenario;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestResultFactory extends Factory
{
    protected $model = TestResult::class;

    public function definition(): array
    {
        return [
            'test_scenario_id' => TestScenario::factory(),
            'service_type' => $this->faker->randomElement(ServiceType::values()),
            'status' => $this->faker->randomElement([
                TestResult::STATUS_SUCCESS,
                TestResult::STATUS_FAILURE,
            ]),
            'start_time' => now(),
            'end_time' => now()->addSeconds(rand(1, 10)),
            'execution_time_ms' => rand(100, 1000),
        ];
    }

    public function success(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestResult::STATUS_SUCCESS,
        ]);
    }

    public function failure(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestResult::STATUS_FAILURE,
            'error_message' => $this->faker->sentence,
        ]);
    }

    public function forService(ServiceType $serviceType): self
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => $serviceType,
        ]);
    }
}
