<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\User::with(['subscriptionPlan', 'creditBalances.aiModel']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }
        
        $users = $query->latest()->paginate(15)->withQueryString();
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->get();
        
        return view('users.index', compact('users', 'plans'));
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'subscription_id' => 'nullable|exists:subscription_plans,id',
        ]);

        $oldSubscriptionId = $user->subscription_id;
        $user->update($data);

        // If admin manually assigns a new plan, grant the credits
        if ($request->filled('subscription_id') && $oldSubscriptionId != $request->subscription_id) {
            $plan = \App\Models\SubscriptionPlan::with('creditAllocations')->find($request->subscription_id);
            foreach ($plan->creditAllocations as $allocation) {
                $user->creditBalances()->updateOrCreate(
                    ['model_id' => $allocation->model_id],
                    [
                        'credits_remaining' => \DB::raw('credits_remaining + ' . $allocation->credits_granted),
                        'credits_total' => \DB::raw('credits_total + ' . $allocation->credits_granted)
                    ]
                );
            }
        }

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(\App\Models\User $user)
    {
        // Don't allow admin to delete themselves (assuming ID 1 is main admin)
        if ($user->id === 1 || $user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete this admin user.');
        }
        
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
