<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'is_super_admin',
        'avatar',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        if (array_key_exists('is_super_admin', $this->attributes) && (bool) $this->attributes['is_super_admin']) {
            return true;
        }

        return $this->roleModel?->slug === 'super_admin';
    }

    public function isManager(): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        return $this->role === 'manager' || $this->roleModel?->slug === 'manager';
    }

    public function canDo(string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isManager()) {
            return true;
        }

        $role = $this->relationLoaded('roleModel')
            ? $this->roleModel
            : $this->roleModel()->with('permissions')->first();

        return (bool) $role?->permissions->contains('key', $permission);
    }

    public function allowedCompanyIds(): ?array
    {
        if ($this->isSuperAdmin() || $this->isManager()) {
            return null;
        }

        $companyIds = $this->companies()->pluck('companies.id')->all();

        return $companyIds ?: null;
    }

    public function canAccessCompany(?int $companyId): bool
    {
        if (! $companyId || $this->isSuperAdmin() || $this->isManager()) {
            return true;
        }

        $allowedCompanyIds = $this->allowedCompanyIds();

        return $allowedCompanyIds === null || in_array($companyId, $allowedCompanyIds, true);
    }

    public function getIsSuperAdminAttribute(): bool
    {
        return $this->isSuperAdmin();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return asset('storage/'.$this->avatar);
    }
}
