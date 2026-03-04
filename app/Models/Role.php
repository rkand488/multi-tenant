<?php

namespace App\Models;

use App\Tenancy\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use ScopedByTenant;

    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
