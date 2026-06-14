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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
        return (bool) $this->is_super_admin;
    }

    public function isManager(): bool
    {
        return $this->isSuperAdmin()
            || $this->role === 'manager'
            || $this->roleModel?->slug === 'manager'
            || $this->roleModel?->slug === 'super_admin';
    }

    public function canDo(string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isManager()) {
            return true;
        }

        $role = $this->relationLoaded('roleModel') ? $this->roleModel : $this->roleModel()->with('permissions')->first();

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
        if ((bool) ($this->attributes['is_admin'] ?? false)) {
            return true;
        }

        if (method_exists($this, 'roleModel') && $this->roleModel) {
            return $this->roleModel->slug === 'super_admin';
        }

        return $this->role === 'manager' && (bool) ($this->is_admin ?? false);
    }
    public function canManageUsers(): bool
    {
        if ($this->is_super_admin ?? false) {
            return true;
        }

        $slug = $this->roleModel?->slug ?? $this->role;

        return in_array($slug, ['super_admin', 'manager'], true);
    }
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isManager()) {
            return true;
        }

        return $this->roleModel
            ?->permissions()
            ->where('key', $permission)
            ->exists() ?? false;
    }

    public function can($abilities, $arguments = [])
    {
        if (is_string($abilities) && ($this->isSuperAdmin() || $this->isManager())) {
            return true;
        }

        return parent::can($abilities, $arguments);
    }
}
