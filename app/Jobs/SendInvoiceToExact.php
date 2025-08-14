<?php

namespace App\Jobs;

use App\DTOs\CreateInvoiceDto;
use App\Repositories\Contracts\ExternalServiceCallLogRepositoryInterface;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Services\ExactOnline\ExactOnlineInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceToExact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 10;
    public $tries = 3;

    public function __construct(
        private CreateInvoiceDto $invoiceDto,
        private int $newClientRequestId,
        private int $invoiceId
    ) {
        //
    }

    public function handle(
        ExactOnlineInterface $exactService,
        ExternalServiceCallLogRepositoryInterface $externalServiceCallLogRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ): void {
        try {
            $response = $exactService->sendInvoice($this->invoiceDto);

            $externalServiceCallLogRepository->create([
                'client_request_log_id' => $this->newClientRequestId,
                'attempt_no'            => $this->attempts(),
                'external_url'          => 'https://api.exact.com/invoices',
                'response_status_code'  => $response->status,
                'error_message'         => $response->body['message'] ?? null,
            ]);

            // Map HTTP status → handler method
            $handlers = [
                500 => 'handleStatus500',
                429 => 'handleStatus429',
                409 => 'handleStatus409',
            ];

            $method = $handlers[$response->status] ?? 'handleStatusSuccess';
            $this->{$method}($invoiceRepository, $response);
        } catch (\Throwable $e) {
            Log::error('Failed to send invoice to Exact Online', [
                'client_request_id' => $this->newClientRequestId,
                'error'             => $e->getMessage(),
                'attempt'           => $this->attempts(),
            ]);

            $externalServiceCallLogRepository->create([
                'client_request_log_id' => $this->newClientRequestId,
                'attempt_no'            => $this->attempts(),
                'external_url'          => 'https://api.exact.com/invoices',
                'response_status_code'  => 500,
                'error_message'         => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                Log::error('Max attempts reached for invoice', [
                    'client_request_log_id' => $this->newClientRequestId,
                ]);
            }

            throw $e;
        }
    }

    protected function handleStatus500(
        InvoiceRepositoryInterface $invoiceRepository,
        $response
    ): void {
        $invoiceRepository->updateStatus($this->invoiceId, ['status' => 'failed']);
        $this->release(10); // 10s for test; tune for prod
    }

    protected function handleStatus429(
        InvoiceRepositoryInterface $invoiceRepository,
        $response
    ): void {
        $invoiceRepository->updateStatus($this->invoiceId, ['status' => 'failed']);
        $this->release(5); // 5s for test; tune for prod
    }

    protected function handleStatus409(
        InvoiceRepositoryInterface $invoiceRepository,
        $response
    ): void {
        // Duplicate — mark forwarded, do not retry
        // It was already forwarded: the previous attempt succeeded, but a network issue caused the job to fail without a response.
        $invoiceRepository->updateStatus($this->invoiceId, [
            'status'       => 'forwarded',
            'forwarded_at' => Carbon::now(),
            'exact_id'     => $response->external_id,
        ]);
    }

    protected function handleStatusSuccess(
        InvoiceRepositoryInterface $invoiceRepository,
        $response
    ): void {
        $invoiceRepository->updateStatus($this->invoiceId, [
            'status'       => 'forwarded',
            'forwarded_at' => Carbon::now(),
            'exact_id'     => $response->external_id,
        ]);
    }
}
