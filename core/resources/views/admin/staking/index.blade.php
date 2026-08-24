@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <!-- Total Staked -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--primary has-link overflow-hidden box--shadow2">
            <a href="{{ route('admin.staking.stakes') }}" class="item-link"></a>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-wallet f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Total Platform Staked')</span>
                        <h2 class="text-white">${{ number_format($totalStaked, 2) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Yield Distributed -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--success has-link overflow-hidden box--shadow2">
            <a href="{{ route('admin.staking.stakes') }}" class="item-link"></a>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-hand-holding-usd f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Total Yield Distributed')</span>
                        <h2 class="text-white">+${{ number_format($totalRewards, 2) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Stakes -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--warning has-link overflow-hidden box--shadow2">
            <a href="{{ route('admin.staking.stakes') }}?status=active" class="item-link"></a>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-lock f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Active Stake Positions')</span>
                        <h2 class="text-white">{{ $activeStakesCount }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staking Pools -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--info has-link overflow-hidden box--shadow2">
            <a href="{{ route('admin.staking.pools') }}" class="item-link"></a>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-cubes f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Configured Pools')</span>
                        <h2 class="text-white">{{ $totalPoolsCount }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stakes Table -->
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">@lang('Recent Staking Positions')</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.staking.pools') }}" class="btn btn-sm btn--outline-primary">
                        <i class="las la-cubes"></i> @lang('Manage Pools')
                    </a>
                    <a href="{{ route('admin.staking.stakes') }}" class="btn btn-sm btn--primary">
                        <i class="las la-list"></i> @lang('View All Stakes & Inject')
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Vault Pool')</th>
                                <th>@lang('Principal')</th>
                                <th>@lang('APY Rate')</th>
                                <th>@lang('Accumulated Yield')</th>
                                <th>@lang('Term')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentStakes as $stake)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ @$stake->user->fullname }}</span>
                                        <br>
                                        <span class="small">
                                            <a href="{{ route('admin.users.detail', @$stake->user_id) }}"><span>@</span>{{ @$stake->user->username }}</a>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ @$stake->pool->name ?? 'Staking Vault' }}</span>
                                        <br>
                                        <span class="badge badge--dark">{{ @$stake->pool->token_symbol ?? 'USDT' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">${{ number_format($stake->principal_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge--info">{{ @$stake->pool->apy_rate ?? 12 }}% APY</span>
                                    </td>
                                    <td>
                                        <span class="text--success fw-bold">+${{ number_format($stake->accumulated_rewards, 2) }}</span>
                                    </td>
                                    <td>
                                        <span>{{ @$stake->pool->lock_period_days > 0 ? @$stake->pool->lock_period_days . ' Days' : 'Flexible' }}</span>
                                    </td>
                                    <td>
                                        @if($stake->status == 'active')
                                            <span class="badge badge--success">@lang('Active')</span>
                                        @else
                                            <span class="badge badge--dark">@lang('Completed')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.staking.stakes') }}?user_id={{ $stake->user_id }}" class="btn btn-sm btn--outline-primary">
                                            <i class="la la-eye"></i> @lang('Details')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">@lang('No staking positions recorded yet')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection