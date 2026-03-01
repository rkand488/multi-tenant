<?php

namespace App\Central\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks per-tenant metered feature consumption within a billing period.
 *
 * @property int $id
 * @property string $tenant_id
 * @property string|null $subscription_id
 * @property string $feature_key
 * @property int $quantity
 * @property \Carbon\Carbon $period_start
 * @property \Carbon\Carbon $period_end
 */
class Usage extends Model
{
    use HasFactory;

    /** @var string */
    protected $connection = 'central';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'feature_key',
        'quantity',
        'period_start',
        'period_end',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<Subscription, $this> */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isWithinCurrentPeriod(): bool
    {
        return now()->between($this->period_start, $this->period_end);
    }
}
