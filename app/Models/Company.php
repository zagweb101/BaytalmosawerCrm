<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'activity',
        'lead_goal',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function offerings(): HasMany
    {
        return $this->hasMany(CompanyOffering::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
