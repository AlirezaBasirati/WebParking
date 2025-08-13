<?php

namespace App\Repositories\Eloquent;

use App\Models\ClientRequestLog;
use App\Repositories\Contracts\ClientRequestLogRepositoryInterface;

class ClientRequestLogRepository implements ClientRequestLogRepositoryInterface
{
    /**
     * Create a new ClientRequestLog entry.
     *
     * @param array $data
     * @return ClientRequestLog
     */
    public function create(array $data): ClientRequestLog
    {
        return ClientRequestLog::create($data);
    }

    /**
     * Update a ClientRequestLog entry by request UUID.
     *
     * @param string $requestUuid
     * @param array $data
     * @return bool
     */
    public function updateByRequestUuid(string $requestUuid, array $data): bool
    {
        $result = ClientRequestLog::where('request_uuid', $requestUuid)->update($data);
        return $result > 0;
    }

    /**
     * Find a ClientRequestLog entry by request UUID.
     *
     * @param string $requestUuid
     * @return ClientRequestLog|null
     */
    public function findByRequestUuid(string $requestUuid): ?ClientRequestLog
    {
        return ClientRequestLog::where('request_uuid', $requestUuid)->first();
    }
}
