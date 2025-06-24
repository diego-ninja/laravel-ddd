<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the Migrations.
     */
    public function up(): void
    {
        Schema::create('domain_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type');
            $table->uuid('aggregate_id');
            $table->string('aggregate_type');
            $table->json('payload');
            $table->timestamp('occurred_on');
            $table->timestamp('processed_at')->nullable();
            $table->string('status')->default('pending'); // pending, processed, failed
            $table->text('error_message')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();

            // Indexes for performance
            $table->index(['aggregate_id', 'occurred_on']);
            $table->index(['event_type', 'status']);
            $table->index(['status', 'occurred_on']);
            $table->index('aggregate_type');
        });
    }

    /**
     * Reverse the Migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_events');
    }
};
