<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central migration — usages table.
 *
 * Tracks per-tenant metered feature consumption within a billing period.
 * A unique constraint on (tenant_id, feature_key, period_start) ensures
 * one row per feature per period, making increments a simple UPDATE.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('usages', function (Blueprint $table): void {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->uuid('subscription_id')->nullable()->index();
            $table->string('feature_key')->index();
            $table->unsignedBigInteger('quantity')->default(0);
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->timestamps();

            $table->unique(['tenant_id', 'feature_key', 'period_start']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('usages');
    }
};
