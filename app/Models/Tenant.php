<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'company_name', 'subdomain', 'email', 'phone', 'address', 'industry',
        'plan', 'status', 'max_employees',
        'billing_email', 'billing_name', 'billing_phone',
        'ecocash_number', 'zipit_account',
        'trial_ends_at', 'subscription_starts_at', 'subscription_expires_at',
        'trial_reminder_sent_at', 'subscription_reminder_sent_at',
        'account_balance_usd', 'upcoming_plan',
    ];

    protected $casts = [
        'trial_ends_at'                   => 'datetime',
        'subscription_starts_at'          => 'datetime',
        'subscription_expires_at'         => 'datetime',
        'trial_reminder_sent_at'          => 'datetime',
        'subscription_reminder_sent_at'   => 'datetime',
    ];

    // =====================
    // RELATIONSHIPS
    // =====================

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // =====================
    // STATUS HELPERS
    // =====================

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->subscription_expires_at?->isFuture();
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isWithinGracePeriod(): bool
    {
        // 7-day grace after subscription_expires_at
        if ($this->status === 'active' && $this->subscription_expires_at?->isPast()) {
            return $this->subscription_expires_at->diffInDays(now()) <= 7;
        }
        return false;
    }

    public function canAccess(): bool
    {
        return $this->isActive() || $this->isOnTrial() || $this->isWithinGracePeriod();
    }

    public function getPlanLabel(): string
    {
        return match($this->plan) {
            'starter'   => 'Starter',
            'business'  => 'Business',
            'corporate' => 'Corporate',
            default     => ucfirst($this->plan),
        };
    }

    public function getPendingInvoice(): ?Invoice
    {
        return $this->invoices()->where('status', 'pending')->latest()->first();
    }

    public function getLatestInvoice(): ?Invoice
    {
        return $this->invoices()->latest()->first();
    }

    public function hasFeature(string $featureName): bool
    {
        $plan = SubscriptionPlan::where('slug', $this->plan)->first();
        if (!$plan || !$plan->features) {
            return false;
        }

        return in_array($featureName, $plan->features);
    }
}
