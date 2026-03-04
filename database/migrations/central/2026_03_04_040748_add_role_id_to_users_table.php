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
        Schema::connection('central')->table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id')->nullable()->after('tenant_id');
            $table->index('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('users', function (Blueprint $table): void {
            $table->dropIndex(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
