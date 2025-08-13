<?php

namespace Database\Factories;

use App\Models\ClientRequestLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientRequestLog>
 */
class ClientRequestLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_uuid' => Str::uuid()->toString(),
            'customer_id' => $this->faker->numberBetween(1, 100),
            'request_url' => $this->faker->url(),
            'request_method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            'request_headers' => [
                'User-Agent' => $this->faker->userAgent(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'request_body' => [
                'test' => 'data',
            ],
            'validation_error' => null,
            'invoice_id' => null,
        ];
    }

    /**
     * Indicate that the request has validation errors.
     */
    public function withValidationError(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_error' => json_encode([
                'invoice_number' => ['The invoice number field is required.'],
                'customer_id' => ['The customer id field is required.'],
            ]),
        ]);
    }

    /**
     * Indicate that the request is associated with an invoice.
     */
    public function withInvoice(int $invoiceId): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $invoiceId,
        ]);
    }
}
