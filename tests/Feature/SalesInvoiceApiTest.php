<?php

namespace Tests\Feature;

use App\Jobs\SendInvoiceToExact;
use App\Models\ClientRequestLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SalesInvoiceApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }


    public function test_it_can_create_sales_invoice_successfully()
    {
        $invoiceData = [
            'invoice_number' => 'INV-001',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
            'lines' => [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 2,
                    'unit_price' => 50.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/sales-invoices', $invoiceData);

        $response->assertStatus(200);
        $response->assertJsonFragment(['Your Request Has Dispatched On Queue.']);

        // Assert job was dispatched
        Queue::assertPushed(SendInvoiceToExact::class);

        // Assert database records were created
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-001',
            'customer_id' => 1,
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
        ]);

        $this->assertDatabaseHas('invoice_lines', [
            'line_no' => 1,
            'description' => 'Product A',
            'quantity' => 2,
            'unit_price' => 50.00,
            'vat_code' => 'VAT20',
            'line_total' => 100.00,
        ]);
    }


    public function test_it_validates_required_fields()
    {
        $response = $this->postJson('/api/sales-invoices', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'invoice_number',
            'customer_id',
            'invoice_date',
            'due_date',
            'currency',
            'subtotal',
            'tax',
            'total',
            'lines',
        ]);
    }


    public function test_it_validates_invoice_date_before_due_date()
    {
        $invoiceData = [
            'invoice_number' => 'INV-002',
            'customer_id' => 1,
            'invoice_date' => '2023-12-31',
            'due_date' => '2023-12-01', // Due date before invoice date
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
            'lines' => [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/sales-invoices', $invoiceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['due_date']);
    }


    public function test_it_validates_total_equals_subtotal_plus_tax()
    {
        $invoiceData = [
            'invoice_number' => 'INV-003',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 125.00, // Incorrect total
            'lines' => [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/sales-invoices', $invoiceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['total']);
    }


    public function test_it_validates_lines_array_has_minimum_one_item()
    {
        $invoiceData = [
            'invoice_number' => 'INV-004',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
            'lines' => [], // Empty lines array
        ];

        $response = $this->postJson('/api/sales-invoices', $invoiceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lines']);
    }


    public function test_it_validates_line_items_required_fields()
    {
        $invoiceData = [
            'invoice_number' => 'INV-005',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
            'lines' => [
                [
                    // Missing required fields
                    'description' => 'Product A',
                ],
            ],
        ];

        $response = $this->postJson('/api/sales-invoices', $invoiceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'lines.0.line_no',
            'lines.0.quantity',
            'lines.0.unit_price',
            'lines.0.vat_code',
            'lines.0.line_total',
        ]);
    }


    public function test_it_validates_currency_is_three_characters()
    {
        $invoiceData = [
            'invoice_number' => 'INV-006',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USDD', // 4 characters
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
            'lines' => [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/sales-invoices', $invoiceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['currency']);
    }


    public function test_it_validates_quantity_and_prices_are_positive()
    {
        $invoiceData = [
            'invoice_number' => 'INV-007',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
            'lines' => [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => -1, // Negative quantity
                    'unit_price' => -10, // Negative price
                    'vat_code' => 'VAT20',
                    'line_total' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/sales-invoices', $invoiceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'lines.0.quantity',
            'lines.0.unit_price',
        ]);
    }


    public function test_it_creates_request_log_with_uuid_header()
    {
        $invoiceData = [
            'invoice_number' => 'INV-008',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
            'lines' => [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/sales-invoices', $invoiceData);

        $response->assertStatus(200);

        // Assert log was created in the database
        $this->assertDatabaseHas('client_request_logs', [
            'request_url' => url('/api/sales-invoices'),
            'request_method' => 'POST',
        ]);

        // Get the created log to verify UUID was generated
        $log = ClientRequestLog::where('request_url', url('/api/sales-invoices'))->first();
        $this->assertNotNull($log);
        $this->assertNotNull($log->request_uuid);
        $this->assertNotNull($log->id);
    }
}
