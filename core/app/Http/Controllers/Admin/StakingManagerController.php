<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StakingConfiguration;
use App\Models\StakingPool;
use App\Models\Stake;
use Illuminate\Http\Request;

class StakingManagerController extends Controller
{
    public function index()
    {
        $pageTitle = 'Staking Manager';
        $tokens = StakingConfiguration::with('pools')->get();
        $totalStaked = Stake::where('status', 'active')->sum('current_amount');
        $totalStakers = Stake::where('status', 'active')->distinct('user_id')->count();
        $totalRewardsPaid = StakingRewardHistory::where('type', 'regular')->sum('reward_amount');

        return view('admin.staking.index', compact('pageTitle', 'tokens', 'totalStaked', 'totalStakers', 'totalRewardsPaid'));
    }

    public function createToken(Request $request)
    {
        $request->validate([
            'token_symbol' => 'required|unique:staking_configurations',
            'token_name' => 'required',
            'min_amount' => 'required|numeric|gt:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'early_unstake_penalty_percentage' => 'required|numeric|between:0,100',
            'allows_compound' => 'required|boolean'
        ]);

        StakingConfiguration::create($request->all());

        $notify[] = ['success', 'Staking token added successfully'];
        return back()->withNotify($notify);
    }

    public function createPool(Request $request)
    {
        $request->validate([
            'configuration_id' => 'required|exists:staking_configurations,id',
            'type' => 'required|in:flexible,locked',
            'lock_period_days' => 'required_if:type,locked|integer|min:0',
            'apy_rate' => 'required|numeric|between:0,1000'
        ]);

        StakingPool::create($request->all());

        $notify[] = ['success', 'Staking pool created successfully'];
        return back()->withNotify($notify);
    }

    public function updatePool(Request $request, StakingPool $pool)
    {
        $request->validate([
            'apy_rate' => 'required|numeric|between:0,1000',
            'is_active' => 'required|boolean'
        ]);

        $pool->update($request->only(['apy_rate', 'is_active']));

        $notify[] = ['success', 'Pool updated successfully'];
        return back()->withNotify($notify);
    }

    public function stakes()
    {
        $pageTitle = 'All Stakes';
        $stakes = Stake::with(['user', 'pool.configuration'])
            ->latest()
            ->paginate(getPaginate());

        return view('admin.staking.stakes', compact('pageTitle', 'stakes'));
    }

    public function statistics()
    {
        $pageTitle = 'Staking Statistics';
        
        $stats = [
            'total_staked' => Stake::where('status', 'active')->sum('current_amount'),
            'total_rewards' => StakingRewardHistory::where('type', 'regular')->sum('reward_amount'),
            'total_penalties' => StakingRewardHistory::where('type', 'early_unstake_penalty')->sum('reward_amount'),
            'total_compounds' => StakingRewardHistory::where('type', 'compound')->sum('reward_amount'),
            'active_stakes' => Stake::where('status', 'active')->count(),
            'completed_stakes' => Stake::where('status', 'completed')->count(),
            'unique_stakers' => Stake::distinct('user_id')->count(),
        ];

        $monthlyStats = StakingRewardHistory::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(reward_amount) as total_rewards')
            ->where('type', 'regular')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('admin.staking.statistics', compact('pageTitle', 'stats', 'monthlyStats'));
    }
    public function pools()
{
    $pageTitle = 'Staking Pools';
    $pools = StakingPool::with(['configuration'])
        ->latest()
        ->paginate(getPaginate());
    
    $tokens = StakingConfiguration::active()->get();

    return view('admin.staking.pools', compact('pageTitle', 'pools', 'tokens'));
}
}