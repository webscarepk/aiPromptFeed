<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionApiController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->with(['creditAllocations.aiModel:id,name,slug,cost_per_use'])
            ->get();

        return response()->json(['plans' => $plans]);
    }

    public function mySubscription(Request $request)
    {
        $user = $request->user()->load('subscriptionPlan');

        return response()->json([
            'subscription' => $user->subscriptionPlan
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        $user->subscription_id = $plan->id;
        $user->save();

        // Grant credits based on plan
        foreach ($plan->creditAllocations as $allocation) {
            $user->creditBalances()->updateOrCreate(
                ['model_id' => $allocation->model_id],
                [
                    'credits_remaining' => \DB::raw('credits_remaining + ' . $allocation->credits_granted),
                    'credits_total' => \DB::raw('credits_total + ' . $allocation->credits_granted)
                ]
            );
        }

        return response()->json([
            'message' => 'Subscribed successfully',
            'subscription' => $plan
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $user = $request->user();
        $user->subscription_id = null;
        $user->save();

        return response()->json(['message' => 'Unsubscribed successfully']);
    }

    public function myCredits(Request $request)
    {
        $credits = $request->user()->creditBalances()
            ->with('aiModel:id,name,slug')
            ->get()
            ->map(function ($balance) {
                return [
                    'model_id' => $balance->model_id,
                    'model_name' => $balance->aiModel->name ?? 'Unknown',
                    'credits_remaining' => $balance->credits_remaining,
                    'credits_total' => $balance->credits_total
                ];
            });

        return response()->json(['credits' => $credits]);
    }
}
