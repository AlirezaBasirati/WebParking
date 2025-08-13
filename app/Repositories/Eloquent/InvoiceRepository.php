<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function create(array $invoiceData, array $linesData = []): Invoice
    {
        return DB::transaction(function () use ($invoiceData, $linesData) {
            // Create the invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceData['invoice_number'],
                'customer_id' => $invoiceData['customer_id'],
                'invoice_date' => $invoiceData['invoice_date'],
                'due_date' => $invoiceData['due_date'],
                'currency' => $invoiceData['currency'],
                'subtotal' => $invoiceData['subtotal'],
                'tax' => $invoiceData['tax'],
                'total' => $invoiceData['total'],
                'status' => 'draft',
            ]);

            // Create each invoice line via the relationship
            foreach ($linesData as $line) {
                $invoice->lines()->create([
                    'line_no' => $line['line_no'],
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'vat_code' => $line['vat_code'],
                    'line_total' => $line['line_total'],
                ]);
            }

            return $invoice;
        });
    }

    public function updateStatus(int $id, array $data): bool
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return false;
        }

        return $invoice->update($data);
    }

    public function allInvoices(): ?Collection
    {
        return Invoice::with(["lines"])->get();
    }

    public function invoiceWithLog(): ?Collection
    {
        return Invoice::with(['clientRequestlog.externalCalls'])->get();
    }
}
