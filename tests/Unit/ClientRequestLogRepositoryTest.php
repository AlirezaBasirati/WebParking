<?php

namespace Tests\Unit;

use App\Models\ClientRequestLog;
use App\Models\Invoice;
use App\Repositories\Eloquent\ClientRequestLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientRequestLogRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private ClientRequestLogRepository $clientRequestLogRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientRequestLogRepository = new ClientRequestLogRepository();
    }


    public function test_it_can_create_client_request_log()
    {
        $logData = [
            'request_uuid' => 'test-uuid-123',
            'customer_id' => 1,
            'request_url' => 'https://example.com/api/test',
            'request_method' => 'POST',
            'request_headers' => ['Content-Type' => 'application/json'],
            'request_body' => ['test' => 'data'],
            'validation_error' => null,
            'invoice_id' => null,
        ];

        $log = $this->clientRequestLogRepository->create($logData);

        $this->assertInstanceOf(ClientRequestLog::class, $log);
        $this->assertEquals('test-uuid-123', $log->request_uuid);
        $this->assertEquals(1, $log->customer_id);
        $this->assertEquals('https://example.com/api/test', $log->request_url);
        $this->assertEquals('POST', $log->request_method);
        $this->assertEquals(['Content-Type' => 'application/json'], $log->request_headers);
        $this->assertEquals(['test' => 'data'], $log->request_body);
    }


    public function test_it_can_update_client_request_log_by_uuid()
    {
        $log = ClientRequestLog::factory()->create();

        $updateData = [
            'customer_id' => 999,
        ];

        $result = $this->clientRequestLogRepository->updateByRequestUuid(
            $log->request_uuid,
            $updateData
        );

        $this->assertTrue($result);

        $log->refresh();
        $this->assertEquals(999, $log->customer_id);
    }


    public function test_it_returns_false_when_updating_nonexistent_uuid()
    {
        $result = $this->clientRequestLogRepository->updateByRequestUuid(
            'nonexistent-uuid',
            ['customer_id' => 999]
        );

        $this->assertFalse($result);
    }


    public function test_it_can_find_client_request_log_by_uuid()
    {
        $log = ClientRequestLog::factory()->create();

        $foundLog = $this->clientRequestLogRepository->findByRequestUuid($log->request_uuid);

        $this->assertInstanceOf(ClientRequestLog::class, $foundLog);
        $this->assertEquals($log->id, $foundLog->id);
        $this->assertEquals($log->request_uuid, $foundLog->request_uuid);
    }


    public function test_it_returns_null_when_finding_nonexistent_uuid()
    {
        $foundLog = $this->clientRequestLogRepository->findByRequestUuid('nonexistent-uuid');

        $this->assertNull($foundLog);
    }


    public function test_it_can_update_validation_error()
    {
        $log = ClientRequestLog::factory()->create();

        $validationError = json_encode([
            'invoice_number' => ['The invoice number field is required.'],
            'customer_id' => ['The customer id field is required.'],
        ]);

        $result = $this->clientRequestLogRepository->updateByRequestUuid(
            $log->request_uuid,
            ['validation_error' => $validationError]
        );

        $this->assertTrue($result);

        $log->refresh();
        $this->assertEquals($validationError, $log->validation_error);
    }


    public function test_it_can_update_invoice_id()
    {
        $log = ClientRequestLog::factory()->create();
        $invoice = Invoice::factory()->create();

        $result = $this->clientRequestLogRepository->updateByRequestUuid(
            $log->request_uuid,
            ['invoice_id' => $invoice->id]
        );

        $this->assertTrue($result);

        $log->refresh();
        $this->assertEquals($invoice->id, $log->invoice_id);
    }


    public function test_it_can_update_customer_id()
    {
        $log = ClientRequestLog::factory()->create();

        $result = $this->clientRequestLogRepository->updateByRequestUuid(
            $log->request_uuid,
            ['customer_id' => 789]
        );

        $this->assertTrue($result);

        $log->refresh();
        $this->assertEquals(789, $log->customer_id);
    }
}
