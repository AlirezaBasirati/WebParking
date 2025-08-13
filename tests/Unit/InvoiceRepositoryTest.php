<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Repositories\Eloquent\InvoiceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvoiceRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private InvoiceRepository $invoiceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceRepository = new InvoiceRepository();
    }


    public function test_it_can_create_invoice_with_lines()
    {
        $invoiceData = [
            'invoice_number' => 'INV-001',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => 300.00,
            'tax' => 60.00,
            'total' => 360.00,
        ];

        $linesData = [
            [
                'line_no' => 1,
                'description' => 'Product A',
                'quantity' => 2,
                'unit_price' => 100.00,
                'vat_code' => 'VAT20',
                'line_total' => 200.00,
            ],
            [
                'line_no' => 2,
                'description' => 'Product B',
                'quantity' => 1,
                'unit_price' => 100.00,
                'vat_code' => 'VAT20',
                'line_total' => 100.00,
            ],
        ];

        $invoice = $this->invoiceRepository->create($invoiceData, $linesData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('INV-001', $invoice->invoice_number);
        $this->assertEquals(1, $invoice->customer_id);
        $this->assertEquals('USD', $invoice->currency);
        $this->assertEquals(300.00, $invoice->subtotal);
        $this->assertEquals(60.00, $invoice->tax);
        $this->assertEquals(360.00, $invoice->total);

        // Assert invoice lines were created
        $this->assertCount(2, $invoice->lines);
        $this->assertEquals(1, $invoice->lines[0]->line_no);
        $this->assertEquals('Product A', $invoice->lines[0]->description);
        $this->assertEquals(2, $invoice->lines[0]->quantity);
        $this->assertEquals(100.00, $invoice->lines[0]->unit_price);
        $this->assertEquals('VAT20', $invoice->lines[0]->vat_code);
        $this->assertEquals(200.00, $invoice->lines[0]->line_total);
    }


    public function test_it_can_create_invoice_without_lines()
    {
        $invoiceData = [
            'invoice_number' => 'INV-002',
            'customer_id' => 2,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'EUR',
            'subtotal' => 100.00,
            'tax' => 20.00,
            'total' => 120.00,
        ];

        $invoice = $this->invoiceRepository->create($invoiceData, []);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('INV-002', $invoice->invoice_number);
        $this->assertEquals(2, $invoice->customer_id);
        $this->assertEquals('EUR', $invoice->currency);
        $this->assertEquals(100.00, $invoice->subtotal);
        $this->assertEquals(20.00, $invoice->tax);
        $this->assertEquals(120.00, $invoice->total);

        // Assert no invoice lines were created
        $this->assertCount(0, $invoice->lines);
    }


    public function test_it_can_update_invoice_status()
    {
        $invoice = Invoice::factory()->create();

        $result = $this->invoiceRepository->updateStatus($invoice->id, [
            'status' => 'sent',
            'exact_id' => 'EXACT-123',
        ]);

        $this->assertTrue($result);

        $invoice->refresh();
        $this->assertEquals('sent', $invoice->status);
        $this->assertEquals('EXACT-123', $invoice->exact_id);
    }


    public function test_it_returns_false_when_updating_nonexistent_invoice()
    {
        $result = $this->invoiceRepository->updateStatus(999, [
            'status' => 'sent',
        ]);

        $this->assertFalse($result);
    }


    public function test_it_creates_invoice_within_database_transaction()
    {
        $invoiceData = [
            'invoice_number' => 'INV-003',
            'customer_id' => 3,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'GBP',
            'subtotal' => 200.00,
            'tax' => 40.00,
            'total' => 240.00,
        ];

        $linesData = [
            [
                'line_no' => 1,
                'description' => 'Product C',
                'quantity' => 1,
                'unit_price' => 200.00,
                'vat_code' => 'VAT20',
                'line_total' => 200.00,
            ],
        ];

        $invoice = $this->invoiceRepository->create($invoiceData, $linesData);

        // Assert invoice exists in database
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'invoice_number' => 'INV-003',
            'customer_id' => 3,
            'currency' => 'GBP',
            'subtotal' => 200.00,
            'tax' => 40.00,
            'total' => 240.00,
        ]);

        // Assert invoice line exists in database
        $this->assertDatabaseHas('invoice_lines', [
            'invoice_id' => $invoice->id,
            'line_no' => 1,
            'description' => 'Product C',
            'quantity' => 1,
            'unit_price' => 200.00,
            'vat_code' => 'VAT20',
            'line_total' => 200.00,
        ]);
    }
}
