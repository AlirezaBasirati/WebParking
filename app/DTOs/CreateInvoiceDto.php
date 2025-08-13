<?php

namespace App\DTOs;

use DateTime;
use Illuminate\Http\Request;

class CreateInvoiceDto
{
    /**
     * @param array $lines Array of invoice line items (could be InvoiceLineDto[] in a richer design)
     * @param string $invoice_number Unique invoice identifier with each customer
     * @param int $customer_id Customer ID
     * @param DateTime $invoice_date Date the invoice was issued
     * @param DateTime $due_date Date the invoice is due
     * @param string $currency ISO currency code (e.g., USD, EUR)
     * @param float $subtotal Amount before tax
     * @param float $tax Tax amount
     * @param float $total Final total amount
     * @param array<int, array<string, mixed>> $lines Array of line items, each an associative array with keys:
     *  - line_no (int)
     *  - description (string)
     *  - quantity (int|float)
     *  - unit_price (float)
     *  - vat_code (string)
     *  - line_total (float)
     */
    public function __construct(
        public array $lines,
        public string $invoice_number,
        public int $customer_id,
        public DateTime $invoice_date,
        public DateTime $due_date,
        public string $currency,
        public float $subtotal,
        public float $tax,
        public float $total,
    ) {}

     /**
     * Create DTO from FormRequest or validated array.
     */
    public static function fromRequest(Request $request): self
    {
        // Try to get validated data first, fall back to all data
        $data = method_exists($request, 'validated') ? $request->validated() : $request->all();

        return new self(
            lines: $data['lines'],
            invoice_number: $data['invoice_number'],
            customer_id: $data['customer_id'],
            invoice_date: new DateTime($data['invoice_date']),
            due_date: new DateTime($data['due_date']),
            currency: $data['currency'],
            subtotal: (float) $data['subtotal'],
            tax: (float) $data['tax'],
            total: (float) $data['total'],
        );
    }

    /**
     * Create DTO from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            lines: $data['lines'],
            invoice_number: $data['invoice_number'],
            customer_id: $data['customer_id'],
            invoice_date: new DateTime($data['invoice_date']),
            due_date: new DateTime($data['due_date']),
            currency: $data['currency'],
            subtotal: (float) $data['subtotal'],
            tax: (float) $data['tax'],
            total: (float) $data['total'],
        );
    }
}
