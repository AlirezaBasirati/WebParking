<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-' . $this->faker->unique()->numberBetween(1000, 9999),
            'customer_id' => $this->faker->numberBetween(1, 100),
            'invoice_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'subtotal' => $this->faker->randomFloat(2, 100, 10000),
            'tax' => $this->faker->randomFloat(2, 10, 1000),
            'total' => function (array $attributes) {
                return $attributes['subtotal'] + $attributes['tax'];
            },
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue']),
            'exact_id' => null,
            'forwarded_at' => null,
        ];
    }

    /**
     * Indicate that the invoice is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the invoice has been sent to Exact.
     */
    public function sentToExact(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'forwarded_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }
}
