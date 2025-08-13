<?php

namespace App\Repositories\Eloquent;

use App\Models\ExternalServiceCallLog;
use App\Repositories\Contracts\ExternalServiceCallLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ExternalServiceCallLogRepository implements ExternalServiceCallLogRepositoryInterface
{
    /**
     * Create a new ExternalServiceCallLog entry.
     *
     * @param array $data
     * @return ExternalServiceCallLog
     */
    public function create(array $data): ExternalServiceCallLog
    {
        return ExternalServiceCallLog::create($data);
    }

    /**
     * Find ExternalServiceCallLog entries by client request log ID.
     *
     * @param int $clientRequestLogId
     * @return Collection
     */
    public function findByClientRequestLogId(int $clientRequestLogId): Collection
    {
        return ExternalServiceCallLog::where('client_request_log_id', $clientRequestLogId)->get();
    }

    /**
     * Update an ExternalServiceCallLog entry by ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $result = ExternalServiceCallLog::where('id', $id)->update($data);
        return $result > 0;
    }

    /**
     * Find an ExternalServiceCallLog entry by ID.
     *
     * @param int $id
     * @return ExternalServiceCallLog|null
     */
    public function findById(int $id): ?ExternalServiceCallLog
    {
        return ExternalServiceCallLog::find($id);
    }
}
