<?php

namespace App\Services\ExactOnline;

use App\DTOs\CreateInvoiceDto;

interface ExactOnlineInterface
{
    /**
     * Send an invoice to Exact Online.
     *
     * @param CreateInvoiceDto $dto
     * @return ExactResponse
     */
    public function sendInvoice(CreateInvoiceDto $dto): ExactResponse;
}
