<?php

namespace App\Central\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    /** @var string */
    protected $connection = 'central';

    /** @var string */
    protected $table = 'domains';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'domain',
        'is_primary',
        'is_verified',
        'verified_at',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
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

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param  \Illuminate\Database\Eloquent\Builder<static>  $query */
    public function scopePrimary(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('is_primary', true);
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<static>  $query */
    public function scopeVerified(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('is_verified', true);
    }
}
