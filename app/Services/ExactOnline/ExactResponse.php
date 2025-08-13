<?php

namespace App\Services\ExactOnline;

class ExactResponse
{
    public int $status;
    public array $body;
    public ?string $external_id;

    public function __construct(int $status, array $body = [], ?string $external_id = null)
    {
        $this->status = $status;
        $this->body = $body;
        $this->external_id = $external_id;
    }

    public static function success(string $externalId): self
    {
        return new self(201, ['message' => 'Invoice created successfully'], $externalId);
    }

    public static function duplicate(string $externalId): self
    {
        return new self(409, ['message' => 'Invoice already exists'], $externalId);
    }

    public static function failure(): self
    {
        return new self(500, ['message' => 'Internal server error'], null);
    }

    public static function rateLimit(): self
    {
        return new self(429, ['message' => 'Rate limit exceeded'], null);
    }
}
