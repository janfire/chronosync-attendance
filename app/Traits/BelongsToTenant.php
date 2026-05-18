<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the trait — auto-assigns tenant_id on create,
     * and applies a global scope so all queries are tenant-scoped.
     */
    public static function bootBelongsToTenant(): void
    {
        // Auto-assign the current tenant on creation
        static::creating(function ($model) {
            if (!$model->tenant_id && app()->bound('current_tenant')) {
                $model->tenant_id = app('current_tenant')->id;
            }
        });

        // Apply global scope to all queries
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('current_tenant')) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    app('current_tenant')->id
                );
            }
        });
    }

    /**
     * Escape hatch: bypass the tenant scope (e.g. for super-admin queries).
     * Usage: User::withoutTenantScope()->all()
     */
    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }

    /**
     * Relationship back to the tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
