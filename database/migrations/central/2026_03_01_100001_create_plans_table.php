<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central migration — plans table.
 *
 * A Plan is a product offering (e.g. Starter, Growth, Enterprise).
 * Feature limits are stored as a JSON blob so they can be extended
 * without schema changes.  Stripe price IDs are nullable and populated
 * once the plan is created in Stripe.
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_monthly')->default(0)->comment('Price in cents (e.g. 2900 = $29.00)');
            $table->unsignedBigInteger('price_yearly')->default(0)->comment('Price in cents per year');
            $table->unsignedSmallInteger('trial_days')->default(14);
            $table->json('features')->nullable()->comment('{"max_users":5,"max_projects":10,"api_access":true}');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('plans');
    }
};
