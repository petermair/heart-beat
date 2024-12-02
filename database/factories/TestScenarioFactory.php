<?php

namespace Database\Factories;

use App\Models\TestScenario;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestScenarioFactory extends Factory
{
    protected $model = TestScenario::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'timeout_seconds' => 60,
        ];
    }
}
