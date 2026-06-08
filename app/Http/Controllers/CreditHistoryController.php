<?php

namespace App\Http\Controllers;

use App\Models\CreditHistory;
use App\Models\UserCreditBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Total earned / spent
        $totalEarned = CreditHistory::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalSpent = abs(CreditHistory::where('user_id', $user->id)
            ->where('amount', '<', 0)
            ->sum('amount'));

        // Current overall balance (sum of all credit_balances)
        $currentBalance = UserCreditBalance::where('user_id', $user->id)
            ->sum('credits_remaining');

        // Today's ad view count
        $todayAdCount = (int) \Illuminate\Support\Facades\Cache::get(
            'watch_ad_count_' . $user->id . '_' . now()->toDateString(), 0
        );

        // Recent transaction history (paginated, filter support)
        $filter = $request->query('filter', 'all');
        $query  = CreditHistory::where('user_id', $user->id)->latest();

        if ($filter === 'earned') {
            $query->where('amount', '>', 0);
        } elseif ($filter === 'spent') {
            $query->where('amount', '<', 0);
        }

        $history = $query->paginate(20);

        // "Ways to Earn" task status
        $waysToEarn = [
            [
                'icon'        => 'email',
                'title'       => 'Verify Email',
                'reward'      => '+10',
                'frequency'   => 'once',
                'claimed'     => CreditHistory::where('user_id', $user->id)
                    ->where('type', 'grant')
                    ->where('description', 'LIKE', '%Verify Email%')
                    ->exists(),
                'description' => 'Verify your email address',
            ],
            [
                'icon'        => 'phone',
                'title'       => 'Verify Phone',
                'reward'      => '+10',
                'frequency'   => 'once',
                'claimed'     => CreditHistory::where('user_id', $user->id)
                    ->where('type', 'grant')
                    ->where('description', 'LIKE', '%Verify Phone%')
                    ->exists(),
                'description' => 'Verify your phone number',
            ],
            [
                'icon'        => 'profile',
                'title'       => 'Complete Profile',
                'reward'      => '+10',
                'frequency'   => 'once',
                'claimed'     => CreditHistory::where('user_id', $user->id)
                    ->where('type', 'grant')
                    ->where('description', 'LIKE', '%Complete Profile%')
                    ->exists(),
                'description' => 'Fill in your profile details',
            ],
            [
                'icon'        => 'streak',
                'title'       => 'Daily Streak',
                'reward'      => '+5-150/day',
                'frequency'   => 'daily',
                'claimed'     => false,
                'description' => 'Claim your daily streak reward',
            ],
            [
                'icon'        => 'ad',
                'title'       => 'Watch Ad',
                'reward'      => '+10',
                'frequency'   => 'daily',
                'claimed'     => $todayAdCount >= 3,
                'today_count' => $todayAdCount,
                'daily_limit' => 3,
                'can_watch'   => $todayAdCount < 3,
                'description' => 'Watch a short ad to earn credits',
                'admob_unit'  => env('ADMOB_REWARDED_AD_UNIT_ID'),
            ],
        ];

        return view('credits.history', compact(
            'history',
            'totalEarned',
            'totalSpent',
            'currentBalance',
            'filter',
            'waysToEarn',
            'todayAdCount'
        ));
    }
}
