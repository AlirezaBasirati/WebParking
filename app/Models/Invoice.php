<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'invoice_date',
        'due_date',
        'currency',
        'subtotal',
        'tax',
        'total',
        'status',
        'exact_id',
        'forwarded_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'forwarded_at' => 'datetime',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }
     public function clientRequestlog(): HasOne
    {
        return $this->hasOne(ClientRequestLog::class);
    }
}
