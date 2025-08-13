<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepositoryInterface
{
    /**
     * Create a new Invoice with optional related InvoiceLines data.
     *
     * @param array $invoiceData
     * @param array $linesData
     * @return Invoice
     */
    public function create(array $invoiceData, array $linesData = []): Invoice;

    /**
     * Update an existing Invoice by ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateStatus(int $id, array $data): bool;


    /**
     * Return all existing Invoice With Its Lines.
     *
     * @return Collection
     */
    public function allInvoices(): ?Collection;

    /**
     * Return all existing Invoice With Its Logs .
     *
     * @return Collection
     */
    public function invoiceWithLog(): ?Collection;
}
