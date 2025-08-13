<?php

namespace Tests\Unit;

use App\DTOs\CreateInvoiceDto;
use DateTime;
use Illuminate\Http\Request;
use Tests\TestCase;

class CreateInvoiceDtoTest extends TestCase
{

    public function test_it_can_create_dto_from_valid_data()
    {
        $dto = new CreateInvoiceDto(
            lines: [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 2,
                    'unit_price' => 100.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 200.00,
                ]
            ],
            invoice_number: 'INV-001',
            customer_id: 1,
            invoice_date: new DateTime('2023-12-01'),
            due_date: new DateTime('2023-12-31'),
            currency: 'USD',
            subtotal: 200.00,
            tax: 40.00,
            total: 240.00,
        );

        $this->assertEquals('INV-001', $dto->invoice_number);
        $this->assertEquals(1, $dto->customer_id);
        $this->assertEquals('USD', $dto->currency);
        $this->assertEquals(200.00, $dto->subtotal);
        $this->assertEquals(40.00, $dto->tax);
        $this->assertEquals(240.00, $dto->total);
        $this->assertCount(1, $dto->lines);
        $this->assertEquals(1, $dto->lines[0]['line_no']);
        $this->assertEquals('Product A', $dto->lines[0]['description']);
    }


    public function test_it_can_create_dto_from_request()
    {
        $requestData = [
            'lines' => [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 2,
                    'unit_price' => 100.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 200.00,
                ]
            ],
            'invoice_number' => 'INV-001',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => '200.00',
            'tax' => '40.00',
            'total' => '240.00',
        ];

        $request = Request::create('/test', 'POST', $requestData);
        
        // Mock the validated method to return our test data
        $request = \Mockery::mock($request);
        $request->shouldReceive('validated')->andReturn($requestData);

        $dto = CreateInvoiceDto::fromRequest($request);

        $this->assertEquals('INV-001', $dto->invoice_number);
        $this->assertEquals(1, $dto->customer_id);
        $this->assertEquals('USD', $dto->currency);
        $this->assertEquals(200.00, $dto->subtotal);
        $this->assertEquals(40.00, $dto->tax);
        $this->assertEquals(240.00, $dto->total);
        $this->assertInstanceOf(DateTime::class, $dto->invoice_date);
        $this->assertInstanceOf(DateTime::class, $dto->due_date);
        $this->assertEquals('2023-12-01', $dto->invoice_date->format('Y-m-d'));
        $this->assertEquals('2023-12-31', $dto->due_date->format('Y-m-d'));
    }


    public function test_it_converts_string_numbers_to_float()
    {
        $requestData = [
            'lines' => [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 2,
                    'unit_price' => 100.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 200.00,
                ]
            ],
            'invoice_number' => 'INV-001',
            'customer_id' => 1,
            'invoice_date' => '2023-12-01',
            'due_date' => '2023-12-31',
            'currency' => 'USD',
            'subtotal' => '200.50',
            'tax' => '40.25',
            'total' => '240.75',
        ];

        $request = Request::create('/test', 'POST', $requestData);
        
        // Mock the validated method to return our test data
        $request = \Mockery::mock($request);
        $request->shouldReceive('validated')->andReturn($requestData);

        $dto = CreateInvoiceDto::fromRequest($request);

        $this->assertIsFloat($dto->subtotal);
        $this->assertIsFloat($dto->tax);
        $this->assertIsFloat($dto->total);
        $this->assertEquals(200.50, $dto->subtotal);
        $this->assertEquals(40.25, $dto->tax);
        $this->assertEquals(240.75, $dto->total);
    }


    public function test_it_handles_multiple_invoice_lines()
    {
        $dto = new CreateInvoiceDto(
            lines: [
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
                    'unit_price' => 50.00,
                    'vat_code' => 'VAT10',
                    'line_total' => 50.00,
                ],
                [
                    'line_no' => 3,
                    'description' => 'Product C',
                    'quantity' => 3,
                    'unit_price' => 25.00,
                    'vat_code' => 'VAT0',
                    'line_total' => 75.00,
                ]
            ],
            invoice_number: 'INV-001',
            customer_id: 1,
            invoice_date: new DateTime('2023-12-01'),
            due_date: new DateTime('2023-12-31'),
            currency: 'USD',
            subtotal: 325.00,
            tax: 45.00,
            total: 370.00,
        );

        $this->assertCount(3, $dto->lines);
        $this->assertEquals(1, $dto->lines[0]['line_no']);
        $this->assertEquals(2, $dto->lines[1]['line_no']);
        $this->assertEquals(3, $dto->lines[2]['line_no']);
        $this->assertEquals('Product A', $dto->lines[0]['description']);
        $this->assertEquals('Product B', $dto->lines[1]['description']);
        $this->assertEquals('Product C', $dto->lines[2]['description']);
    }


    public function test_it_preserves_line_item_data_structure()
    {
        $dto = new CreateInvoiceDto(
            lines: [
                [
                    'line_no' => 1,
                    'description' => 'Product A',
                    'quantity' => 2.5,
                    'unit_price' => 100.00,
                    'vat_code' => 'VAT20',
                    'line_total' => 250.00,
                ]
            ],
            invoice_number: 'INV-001',
            customer_id: 1,
            invoice_date: new DateTime('2023-12-01'),
            due_date: new DateTime('2023-12-31'),
            currency: 'USD',
            subtotal: 250.00,
            tax: 50.00,
            total: 300.00,
        );

        $line = $dto->lines[0];
        $this->assertEquals(1, $line['line_no']);
        $this->assertEquals('Product A', $line['description']);
        $this->assertEquals(2.5, $line['quantity']);
        $this->assertEquals(100.00, $line['unit_price']);
        $this->assertEquals('VAT20', $line['vat_code']);
        $this->assertEquals(250.00, $line['line_total']);
    }
}
