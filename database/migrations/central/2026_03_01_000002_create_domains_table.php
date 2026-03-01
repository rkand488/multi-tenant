<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central database migration — domains table.
 *
 * Stores both auto-generated subdomains ({slug}.app.com) and custom CNAME
 * domains verified by the tenant.
 *
 * Run against the `central` connection:
 *   php artisan migrate --database=central --path=database/migrations/central
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('domains', function (Blueprint $table): void {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->string('domain')->unique()->comment('Full hostname, e.g. acme.app.com or acme.io');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('domains');
    }
};
