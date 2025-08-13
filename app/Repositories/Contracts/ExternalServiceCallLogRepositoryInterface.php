<?php

namespace App\Repositories\Contracts;

use App\Models\ExternalServiceCallLog;
use Illuminate\Database\Eloquent\Collection;

interface ExternalServiceCallLogRepositoryInterface
{
    /**
     * Create a new ExternalServiceCallLog entry.
     *
     * @param array $data
     * @return ExternalServiceCallLog
     */
    public function create(array $data): ExternalServiceCallLog;

    /**
     * Find ExternalServiceCallLog entries by client request log ID.
     *
     * @param int $clientRequestLogId
     * @return Collection
     */
    public function findByClientRequestLogId(int $clientRequestLogId): Collection;

    /**
     * Update an ExternalServiceCallLog entry by ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Find an ExternalServiceCallLog entry by ID.
     *
     * @param int $id
     * @return ExternalServiceCallLog|null
     */
    public function findById(int $id): ?ExternalServiceCallLog;
}
