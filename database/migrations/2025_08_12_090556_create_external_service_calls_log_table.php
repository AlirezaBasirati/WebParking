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
        Schema::create('external_service_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_request_log_id')->constrained('client_request_logs');
            $table->unsignedInteger('attempt_no');
            $table->text('external_url');
            $table->integer('response_status_code')->nullable();
            // $table->json('response_headers')->nullable();
            // $table->longText('response_body')->nullable();
            $table->text('error_message')->nullable();
            // $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            //$table->unique(['client_request_id', 'attempt_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_service_call_logs');
    }
};
