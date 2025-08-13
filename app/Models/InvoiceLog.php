<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'invoice_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_id',
        'attempted_at',
        'response_code',
        'response_body',
        'attempt_number',
        'success',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'attempted_at' => 'datetime',
        'success' => 'boolean',
        'response_body' => 'array', // will JSON decode automatically if stored as JSON
    ];

    /**
     * Relationship: Log belongs to an invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
