<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('tenancy.tenant_connection'))->create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('group');
            $table->string('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection(config('tenancy.tenant_connection'))->dropIfExists('permissions');
    }
};
