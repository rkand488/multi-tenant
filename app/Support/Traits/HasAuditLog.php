<?php

namespace App\Support\Traits;

use App\Tenancy\Observers\AuditObserver;

/**
 * Automatically writes audit log entries whenever a model is created,
 * updated, deleted, or restored.
 *
 * Usage:
 *   class User extends Authenticatable
 *   {
 *       use HasAuditLog;
 *   }
 */
trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        static::observe(AuditObserver::class);
    }

    /**
     * Override in the model to exclude sensitive fields from audit records.
     *
     * @return list<string>
     */
    public function getAuditExcludedAttributes(): array
    {
        return ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];
    }
}
