<?php

namespace App\Central\Models;

use App\Central\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /** @var string */
    protected $connection = 'central';

    /** @var string */
    protected $table = 'tenants';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'status',
        'owner_email',
        'trial_ends_at',
        'db_connection',
        'extra',
    ];

    /** @var list<string> */
    protected $hidden = [
        'db_connection',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'db_connection' => 'array',
            'extra' => 'array',
            'trial_ends_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** @return HasMany<Domain, $this> */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /** @return HasOne<Domain, $this> */
    public function primaryDomain(): HasOne
    {
        return $this->hasOne(Domain::class)->where('is_primary', true);
    }

    /** @return HasMany<Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** @return HasOne<Subscription, $this> */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['trialing', 'active'])
            ->latestOfMany();
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** @return HasMany<Usage, $this> */
    public function usages(): HasMany
    {
        return $this->hasMany(Usage::class);
    }

    /** @return HasMany<TenantFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(TenantFile::class);
    }

    /** @return HasMany<ActivityLog, $this> */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Derive the tenant database name from its slug and the configured prefix.
     */
    public function databaseName(): string
    {
        return config('tenancy.db_prefix').$this->slug;
    }

    /**
     * Return the queue name used for this tenant's jobs.
     */
    public function queueName(): string
    {
        return config('tenancy.queue_prefix').$this->id;
    }

    /**
     * Read a feature limit from the active plan.
     */
    public function planFeature(string $key, mixed $default = null): mixed
    {
        return $this->currentSubscription?->plan?->feature($key) ?? $default;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param  \Illuminate\Database\Eloquent\Builder<static>  $query */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', TenantStatus::Active);
    }
}
