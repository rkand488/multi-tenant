<?php

namespace App\Support\Traits;

use App\Central\Models\ActivityLog;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Request;

/**
 * Provides a convenience method for manually writing activity log entries.
 *
 * Usage:
 *   $this->logActivity('user.invited', 'Invited user john@example.com');
 */
trait HasActivityLog
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function logActivity(string $action, string $description, array $properties = []): void
    {
        /** @var TenantContext $context */
        $context = app(TenantContext::class);
        $tenant = $context->getOrNull();

        if ($tenant === null) {
            return;
        }

        ActivityLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => static::class,
            'subject_id' => $this->getKey(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'meta' => array_merge(['description' => $description], $properties),
        ]);
    }
}
