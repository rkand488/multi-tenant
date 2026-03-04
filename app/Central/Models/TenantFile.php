<?php

namespace App\Central\Models;

use App\Models\User;
use App\Tenancy\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantFile extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFileFactory> */
    use HasFactory;

    use HasUuids;
    use ScopedByTenant;

    protected $connection = 'central';

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'visibility',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_by' => 'integer',
            'size' => 'integer',
            'meta' => 'array',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
