<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformSubscriptionController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('price_usd')->get();
        return view('admin.superadmin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.superadmin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_usd' => 'required|numeric|min:0',
            'max_employees' => 'required|integer|min:0',
            'trial_days' => 'required|integer|min:0',
            'features' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Convert comma-separated string to array
        if (!empty($validated['features'])) {
            $validated['features'] = array_map('trim', explode(',', $validated['features']));
        } else {
            $validated['features'] = [];
        }

        $validated['is_active'] = $request->has('is_active');

        SubscriptionPlan::create($validated);

        return redirect()->route('superadmin.plans.index')->with('success', 'Subscription plan created successfully.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.superadmin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_usd' => 'required|numeric|min:0',
            'max_employees' => 'required|integer|min:0',
            'trial_days' => 'required|integer|min:0',
            'features' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Convert comma-separated string to array
        if (!empty($validated['features'])) {
            $validated['features'] = array_map('trim', explode(',', $validated['features']));
        } else {
            $validated['features'] = [];
        }

        $validated['is_active'] = $request->has('is_active');

        $plan->update($validated);

        return redirect()->route('superadmin.plans.index')->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        // Don't delete if tenants are using this plan
        if (\App\Models\Tenant::where('plan_id', $plan->id)->exists()) {
            return redirect()->route('superadmin.plans.index')->with('error', 'Cannot delete plan because it is currently assigned to one or more tenants.');
        }

        $plan->delete();
        return redirect()->route('superadmin.plans.index')->with('success', 'Subscription plan deleted successfully.');
    }
}
