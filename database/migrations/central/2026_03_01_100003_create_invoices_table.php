<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central migration — invoices table.
 *
 * Tracks billing history.  Amounts are stored in the smallest currency unit
 * (e.g. cents for USD) to avoid floating-point issues.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('subscription_id')->nullable()->index();
            $table->string('number')->unique()->comment('Human-readable invoice number, e.g. INV-0001');
            $table->string('status', 20)->default('open');
            $table->unsignedBigInteger('amount_due')->default(0)->comment('In cents');
            $table->unsignedBigInteger('amount_paid')->default(0)->comment('In cents');
            $table->string('currency', 3)->default('usd');
            $table->string('billing_interval', 10)->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->date('due_date')->nullable();
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('invoices');
    }
};
