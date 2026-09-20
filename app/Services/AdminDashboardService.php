<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Invoice;
use Illuminate\Support\Facades\Queue;

class AdminDashboardService
{
    public function getMetrics(): array
    {
        $now = now();

        return [
            // Total tenants in the system
            'total_tenants' => Tenant::count(),

            // Tenants with an active subscription (status active & expires in the future)
            'active_subscriptions' => Tenant::where('status', 'active')
                ->whereNotNull('subscription_expires_at')
                ->where('subscription_expires_at', '>', $now)
                ->count(),

            // Tenants with a pending subscription (status pending or start date in the future)
            'pending_subscriptions' => Tenant::where('status', 'pending')
                ->orWhere(function ($q) use ($now) {
                    $q->whereNotNull('subscription_starts_at')
                      ->where('subscription_starts_at', '>', $now);
                })
                ->count(),

            // Monthly Recurring Revenue (paid invoices covering the current month)
            'mrr' => Invoice::where('status', 'paid')
                ->whereDate('period_start', '<=', $now->endOfMonth())
                ->whereDate('period_end', '>=', $now->startOfMonth())
                ->sum('amount_usd'),

            // New tenants created in the last 7 days
            'new_tenants' => Tenant::where('created_at', '>=', $now->subDays(7))->count(),

            // Total Users across all tenants
            'total_users' => \App\Models\User::withoutTenantScope()->whereNotNull('tenant_id')->count(),

            // Monthly Revenue Data for Chart (last 6 months)
            'revenue_chart_data' => $this->getRevenueChartData(),

            // Number of jobs waiting in the queue
            'queue_jobs' => Queue::size(),
        ];
    }

    private function getRevenueChartData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            
            $revenue = Invoice::where('status', 'paid')
                ->whereDate('period_start', '<=', $month->endOfMonth())
                ->whereDate('period_end', '>=', $month->startOfMonth())
                ->sum('amount_usd');
                
            $data[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
