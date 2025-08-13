<?php

namespace Database\Factories;

use App\Models\ClientRequestLog;
use App\Models\ExternalServiceCallLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExternalServiceCallLog>
 */
class ExternalServiceCallLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_request_log_id' => ClientRequestLog::factory(),
            'attempt_no' => $this->faker->numberBetween(1, 5),
            'external_url' => $this->faker->url(),
            'response_status_code' => $this->faker->randomElement([200, 201, 400, 401, 500]),
            'error_message' => null,
        ];
    }

    /**
     * Indicate that the service call was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'response_status_code' => 201,
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the service call failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'response_status_code' => $this->faker->randomElement([400, 401, 500]),
            'error_message' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create external service call log for specific client request.
     */
    public function forClientRequest(ClientRequestLog $clientRequestLog): static
    {
        return $this->state(fn (array $attributes) => [
            'client_request_log_id' => $clientRequestLog->id,
        ]);
    }
}
