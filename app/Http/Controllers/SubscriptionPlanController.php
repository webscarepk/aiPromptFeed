<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = \App\Models\SubscriptionPlan::with('creditAllocations.aiModel')->latest()->paginate(15);
        $aiModels = \App\Models\AiModel::where('is_active', true)->get();
        return view('subscription_plans.index', compact('plans', 'aiModels'));
    }

    public function create() { return redirect()->route('subscription-plans.index'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price_usd_cents' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'allocations' => 'nullable|array',
            'allocations.*' => 'nullable|integer|min:0'
        ]);

        $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
        $plan = \App\Models\SubscriptionPlan::create($data);

        if (!empty($data['allocations'])) {
            foreach ($data['allocations'] as $modelId => $credits) {
                if ($credits > 0) {
                    $plan->creditAllocations()->create([
                        'model_id' => $modelId,
                        'credits_granted' => $credits
                    ]);
                }
            }
        }

        return redirect()->route('subscription-plans.index')->with('success', 'Plan created');
    }

    public function show(string $id) { return redirect()->route('subscription-plans.index'); }
    public function edit(string $id) { return redirect()->route('subscription-plans.index'); }

    public function update(Request $request, string $id)
    {
        $plan = \App\Models\SubscriptionPlan::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price_usd_cents' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'allocations' => 'nullable|array',
            'allocations.*' => 'nullable|integer|min:0'
        ]);

        $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
        $plan->update($data);

        $plan->creditAllocations()->delete(); // Clear old allocations
        
        if (!empty($data['allocations'])) {
            foreach ($data['allocations'] as $modelId => $credits) {
                if ($credits > 0) {
                    $plan->creditAllocations()->create([
                        'model_id' => $modelId,
                        'credits_granted' => $credits
                    ]);
                }
            }
        }

        return redirect()->route('subscription-plans.index')->with('success', 'Plan updated');
    }

    public function destroy(string $id)
    {
        \App\Models\SubscriptionPlan::findOrFail($id)->delete();
        return redirect()->route('subscription-plans.index')->with('success', 'Plan deleted');
    }
}
