<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add an index on users.tenant_id so that TenantScope WHERE clauses
     * (applied to every authenticated query) use an index instead of a full
     * table scan.
     */
    public function up(): void
    {
        Schema::connection('central')->table('users', function (Blueprint $table): void {
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('users', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id']);
        });
    }
};
