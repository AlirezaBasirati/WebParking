<?php

namespace App\Services\ExactOnline;

use App\DTOs\CreateInvoiceDto;
use Illuminate\Support\Facades\Http;

class MockExactOnlineService implements ExactOnlineInterface
{
    public function sendInvoice(CreateInvoiceDto $dto): ExactResponse
    {
        // $base = 'https://api.example.com'
        // $response = Http::baseUrl($this->base)
        //     ->body($dto)
        //     ->timeout(5)
        //     ->post("/sale-invoice");

        // TODO: Here must Match The Response With Real Scenario

        $scenario = collect(['success', 'duplicate', 'failure', 'rate_limit'])->random();
        switch ($scenario) {
            case 'success':
                return ExactResponse::success($this->generateExternalId($dto));

            case 'duplicate':
                return ExactResponse::duplicate($this->generateExternalId($dto));

            case 'failure':
                return ExactResponse::failure();

            case 'rate_limit':
                return ExactResponse::rateLimit();

            default:
                throw new \App\Services\ExactOnline\Exceptions\NoScenarioMessageException(
                    "Unknown simulation scenario: {$scenario}"
                );
        }
    }

    private function generateExternalId(CreateInvoiceDto $dto): string
    {
        return 'EXT-' . $dto->invoice_number . '-' . strtoupper(uniqid());
    }
}
