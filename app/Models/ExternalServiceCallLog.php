<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalServiceCallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_request_log_id',
        'attempt_no',
        'external_url',
        'response_status_code',
        'error_message',
    ];

    public function clientRequest(): BelongsTo
    {
        return $this->belongsTo(ClientRequestLog::class, 'client_request_log_id');
    }
}
