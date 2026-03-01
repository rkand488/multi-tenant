<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central database migration — tenants table.
 *
 * Run against the `central` connection:
 *   php artisan migrate --database=central --path=database/migrations/central
 */
return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status', 20)->default('provisioning');
            $table->string('owner_email')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('db_connection')->nullable()->comment('Override DB credentials for dedicated-server tenants.');
            $table->json('extra')->nullable()->comment('Arbitrary metadata: plan features, branding, etc.');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tenants');
    }
};
