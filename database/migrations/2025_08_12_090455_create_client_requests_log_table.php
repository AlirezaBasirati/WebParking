<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_request_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_uuid')->unique();
            $table->string('customer_id')->nullable();
            $table->text('request_url');
            $table->string('request_method', 10);
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->text('validation_error')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_request_logs');
    }
};
