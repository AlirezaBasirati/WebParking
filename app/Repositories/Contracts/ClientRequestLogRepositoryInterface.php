<?php

namespace App\Repositories\Contracts;

use App\Models\ClientRequestLog;

interface ClientRequestLogRepositoryInterface
{
    /**
     * Create a new ClientRequestLog entry.
     *
     * @param array $data
     * @return ClientRequestLog
     */
    public function create(array $data): ClientRequestLog;

    /**
     * Update a ClientRequestLog entry by request UUID.
     *
     * @param string $requestUuid
     * @param array $data
     * @return bool
     */
    public function updateByRequestUuid(string $requestUuid, array $data): bool;

    /**
     * Find a ClientRequestLog entry by request UUID.
     *
     * @param string $requestUuid
     * @return ClientRequestLog|null
     */
    public function findByRequestUuid(string $requestUuid): ?ClientRequestLog;
}
