<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'campaign_id',
        'team_member_id',
        'name',
        'company',
        'email',
        'phone',
        'status',
        'source',
        'interest',
        'service_city',
        'address',
        'social_url',
        'value',
        'payment_status',
        'paid_amount',
        'fulfillment_status',
        'next_follow_up',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'next_follow_up' => 'date',
        ];
    }

    public function getRemainingAmountAttribute(): ?float
    {
        if ($this->value === null) {
            return null;
        }

        return max(0, (float) $this->value - (float) ($this->paid_amount ?? 0));
    }

    public function owningCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class)->latest('due_at');
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CustomerActivity::class)->latest();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(CustomerTask::class)->latest('due_at');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class)->latest();
    }

    public function recordActivity(
        string $type,
        string $title,
        ?string $description = null,
        ?int $teamMemberId = null,
        ?array $metadata = null
    ): CustomerActivity {
        return $this->activities()->create([
            'team_member_id' => $teamMemberId ?? $this->team_member_id,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
