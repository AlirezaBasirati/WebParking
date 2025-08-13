<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_uuid',
        'customer_id',
        'request_url',
        'request_method',
        'request_headers',
        'request_body',
        'validation_error',
        'invoice_id',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
    ];

    public function externalCalls(): HasMany
    {
        return $this->hasMany(ExternalServiceCallLog::class);
    }
}