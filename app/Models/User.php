<?php

namespace App\Models;

use App\Central\Enums\UserRole;
use App\Tenancy\Concerns\ScopedByTenant;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;
    use ScopedByTenant;

    /**
     * The database connection that should be used by the model.
     *
     * @var string|null
     */
    protected $connection = 'central';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tenant_id',
        'role_id',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function customRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isTenantOwner(): bool
    {
        return $this->role === UserRole::TenantOwner;
    }

    public function isTenantUser(): bool
    {
        return $this->role === UserRole::TenantUser;
    }

    public function belongsToTenant(string $tenantId): bool
    {
        return $this->tenant_id === $tenantId;
    }
}
