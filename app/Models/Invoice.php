<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class Invoice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'subscription_plan_id', 'invoice_number',
        'amount_usd', 'currency', 'period_start', 'period_end', 'due_date',
        'status', 'payment_method', 'payment_reference', 'payment_proof_path',
        'paid_at', 'confirmed_by', 'notes',
        'reminder_7d_sent_at', 'reminder_14d_sent_at',
    ];

    protected $casts = [
        'period_start'         => 'date',
        'period_end'           => 'date',
        'due_date'             => 'date',
        'paid_at'              => 'datetime',
        'reminder_7d_sent_at'  => 'datetime',
        'reminder_14d_sent_at' => 'datetime',
        'amount_usd'           => 'decimal:2',
    ];

    // =====================
    // RELATIONSHIPS
    // =====================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // =====================
    // STATUS HELPERS
    // =====================

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isPaid(): bool      { return $this->status === 'paid'; }
    public function isOverdue(): bool   { return $this->status === 'overdue'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function isActuallyOverdue(): bool
    {
        return $this->isPending() && $this->due_date->isPast();
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isActuallyOverdue()) return 0;
        return (int) $this->due_date->diffInDays(now());
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'paid'      => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'pending'   => 'bg-amber-100 text-amber-700 border-amber-200',
            'overdue'   => 'bg-rose-100 text-rose-700 border-rose-200',
            'cancelled' => 'bg-gray-100 text-gray-500 border-gray-200',
            default     => 'bg-gray-100 text-gray-500 border-gray-200',
        };
    }

    // =====================
    // INVOICE NUMBER GENERATOR
    // =====================

    public static function generateInvoiceNumber(): string
    {
        $year  = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'INV-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
