<?php

namespace App\Tenancy\Observers;

use App\Central\Models\AuditLog;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

/**
 * Automatically writes audit log entries for any model that uses the
 * HasAuditLog trait.
 *
 * Register via:
 *   User::observe(AuditObserver::class);
 * or by using the HasAuditLog trait (which does this automatically).
 */
class AuditObserver
{
    public function created(Model $model): void
    {
        $this->writeLog($model, 'created', [], $this->filteredAttributes($model));
    }

    public function updated(Model $model): void
    {
        $old = collect($model->getOriginal())
            ->only(array_keys($model->getDirty()))
            ->except($this->excludedAttributes($model))
            ->toArray();

        $new = collect($model->getDirty())
            ->except($this->excludedAttributes($model))
            ->toArray();

        if (empty($new)) {
            return;
        }

        $this->writeLog($model, 'updated', $old, $new);
    }

    public function deleted(Model $model): void
    {
        $this->writeLog($model, 'deleted', $this->filteredAttributes($model), []);
    }

    public function restored(Model $model): void
    {
        $this->writeLog($model, 'restored', [], $this->filteredAttributes($model));
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    private function writeLog(Model $model, string $event, array $oldValues, array $newValues): void
    {
        /** @var TenantContext $context */
        $context = app(TenantContext::class);
        $tenant = $context->getOrNull();

        if ($tenant === null) {
            return;
        }

        AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => (string) $model->getKey(),
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function filteredAttributes(Model $model): array
    {
        return collect($model->getAttributes())
            ->except($this->excludedAttributes($model))
            ->toArray();
    }

    /**
     * @return list<string>
     */
    private function excludedAttributes(Model $model): array
    {
        if (method_exists($model, 'getAuditExcludedAttributes')) {
            return $model->getAuditExcludedAttributes();
        }

        return ['password', 'remember_token'];
    }
}
