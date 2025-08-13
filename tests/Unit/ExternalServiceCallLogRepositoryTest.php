<?php

namespace Tests\Unit;

use App\Models\ClientRequestLog;
use App\Models\ExternalServiceCallLog;
use App\Repositories\Eloquent\ExternalServiceCallLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExternalServiceCallLogRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private ExternalServiceCallLogRepository $externalServiceCallLogRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->externalServiceCallLogRepository = new ExternalServiceCallLogRepository();
    }


    public function test_it_can_create_external_service_call_log()
    {
        $clientRequestLog = ClientRequestLog::factory()->create();

        $logData = [
            'client_request_log_id' => $clientRequestLog->id,
            'attempt_no' => 1,
            'external_url' => 'https://api.exact.com/invoices',
            'response_status_code' => 201,
            'error_message' => null,
        ];

        $log = $this->externalServiceCallLogRepository->create($logData);

        $this->assertInstanceOf(ExternalServiceCallLog::class, $log);
        $this->assertEquals($clientRequestLog->id, $log->client_request_log_id);
        $this->assertEquals(1, $log->attempt_no);
        $this->assertEquals('https://api.exact.com/invoices', $log->external_url);
        $this->assertEquals(201, $log->response_status_code);
        $this->assertNull($log->error_message);
    }


    public function test_it_can_find_external_service_call_logs_by_client_request_id()
    {
        $clientRequestLog = ClientRequestLog::factory()->create();
        
        // Create multiple external service call logs manually
        $log1 = $this->externalServiceCallLogRepository->create([
            'client_request_log_id' => $clientRequestLog->id,
            'attempt_no' => 1,
            'external_url' => 'https://api.exact.com/invoices',
            'response_status_code' => 201,
            'error_message' => null,
        ]);

        $log2 = $this->externalServiceCallLogRepository->create([
            'client_request_log_id' => $clientRequestLog->id,
            'attempt_no' => 2,
            'external_url' => 'https://api.exact.com/invoices',
            'response_status_code' => 201,
            'error_message' => null,
        ]);

        $log3 = $this->externalServiceCallLogRepository->create([
            'client_request_log_id' => $clientRequestLog->id,
            'attempt_no' => 3,
            'external_url' => 'https://api.exact.com/invoices',
            'response_status_code' => 201,
            'error_message' => null,
        ]);

        $logs = $this->externalServiceCallLogRepository->findByClientRequestLogId($clientRequestLog->id);

        $this->assertCount(3, $logs);
        foreach ($logs as $log) {
            $this->assertEquals($clientRequestLog->id, $log->client_request_log_id);
        }
    }


    public function test_it_returns_empty_collection_for_nonexistent_client_request_id()
    {
        $logs = $this->externalServiceCallLogRepository->findByClientRequestLogId(999);

        $this->assertCount(0, $logs);
    }


    public function test_it_can_update_external_service_call_log()
    {
        $log = ExternalServiceCallLog::factory()->create();

        $updateData = [
            'response_status_code' => 500,
            'error_message' => 'Internal server error',
        ];

        $result = $this->externalServiceCallLogRepository->update($log->id, $updateData);

        $this->assertTrue($result);

        $log->refresh();
        $this->assertEquals(500, $log->response_status_code);
        $this->assertEquals('Internal server error', $log->error_message);
    }


    public function test_it_returns_false_when_updating_nonexistent_log()
    {
        $result = $this->externalServiceCallLogRepository->update(999, [
            'response_status_code' => 500,
        ]);

        $this->assertFalse($result);
    }


    public function test_it_can_find_external_service_call_log_by_id()
    {
        $log = ExternalServiceCallLog::factory()->create();

        $foundLog = $this->externalServiceCallLogRepository->findById($log->id);

        $this->assertInstanceOf(ExternalServiceCallLog::class, $foundLog);
        $this->assertEquals($log->id, $foundLog->id);
        $this->assertEquals($log->client_request_log_id, $foundLog->client_request_log_id);
    }


    public function test_it_returns_null_when_finding_nonexistent_id()
    {
        $foundLog = $this->externalServiceCallLogRepository->findById(999);

        $this->assertNull($foundLog);
    }


    public function test_it_can_log_successful_service_call()
    {
        $clientRequestLog = ClientRequestLog::factory()->create();

        $logData = [
            'client_request_log_id' => $clientRequestLog->id,
            'attempt_no' => 1,
            'external_url' => 'https://api.exact.com/invoices',
            'response_status_code' => 201,
            'error_message' => null,
        ];

        $log = $this->externalServiceCallLogRepository->create($logData);

        $this->assertEquals(201, $log->response_status_code);
        $this->assertNull($log->error_message);
    }


    public function test_it_can_log_failed_service_call()
    {
        $clientRequestLog = ClientRequestLog::factory()->create();

        $logData = [
            'client_request_log_id' => $clientRequestLog->id,
            'attempt_no' => 1,
            'external_url' => 'https://api.exact.com/invoices',
            'response_status_code' => 400,
            'error_message' => 'Bad request - invalid data',
        ];

        $log = $this->externalServiceCallLogRepository->create($logData);

        $this->assertEquals(400, $log->response_status_code);
        $this->assertEquals('Bad request - invalid data', $log->error_message);
    }


    public function test_it_can_log_retry_attempts()
    {
        $clientRequestLog = ClientRequestLog::factory()->create();

        // First attempt
        $firstAttempt = $this->externalServiceCallLogRepository->create([
            'client_request_log_id' => $clientRequestLog->id,
            'attempt_no' => 1,
            'external_url' => 'https://api.exact.com/invoices',
            'response_status_code' => 500,
            'error_message' => 'Service unavailable',
        ]);

        // Second attempt
        $secondAttempt = $this->externalServiceCallLogRepository->create([
            'client_request_log_id' => $clientRequestLog->id,
            'attempt_no' => 2,
            'external_url' => 'https://api.exact.com/invoices',
            'response_status_code' => 201,
            'error_message' => null,
        ]);

        $this->assertEquals(1, $firstAttempt->attempt_no);
        $this->assertEquals(2, $secondAttempt->attempt_no);
        $this->assertEquals(500, $firstAttempt->response_status_code);
        $this->assertEquals(201, $secondAttempt->response_status_code);
    }
}
