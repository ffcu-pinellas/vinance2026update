@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="staking-terminal-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <span class="badge badge--success-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
            <span class="live-pulse-dot"></span> @lang('Institutional Vaults Online')
        </span>
    </div>

    <!-- Main Header Card -->
    <div class="staking-header-card bg--dark-two p-3 p-md-4 rounded-4 mb-3 border-0 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h3 class="text-white fw-bold mb-1 fs-4 d-flex align-items-center gap-2">
                    <i class="las la-coins text--base"></i> @lang('Crypto Staking')
                    @if($apyBoost > 0)
                        <span class="badge badge--warning-soft rounded-pill text--small fw-normal px-2 py-1">
                            <i class="las la-bolt"></i> +{{ $apyBoost }}% @lang('VIP APY Boost Active')
                        </span>
                    @endif
                </h3>
                <p class="text-muted text--small mb-0">@lang('Institutional liquidity pools & fixed-term high-yield earning vaults')</p>
            </div>

            <!-- Balances & Quick Action -->
            <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-between justify-content-md-end flex-wrap">
                <div class="user-wallet-pill bg--dark-three border border-dark rounded-pill px-3 py-2 d-flex align-items-center gap-2 text--small">
                    <span class="text-muted"><i class="las la-wallet text--base"></i> Spot: <strong class="text-white font-mono">${{ number_format($spotBalance, 2) }}</strong></span>
                    <span class="text-muted opacity-50">|</span>
                    <span class="text-muted"><i class="las la-coins text--info"></i> Fund: <strong class="text-white font-mono">${{ number_format($fundingBalance, 2) }}</strong></span>
                </div>

                <button type="button" class="btn btn--base btn-sm rounded-pill px-3 py-2 text-nowrap openPoolsModalBtn">
                    <i class="las la-plus-circle me-1"></i> @lang('Stake Crypto')
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile View Tab Switcher (Visible only on Mobile screens) -->
    <div class="d-md-none mb-3">
        <div class="staking-mobile-nav p-1 rounded-pill bg--dark-two d-flex shadow-sm">
            <button type="button" class="btn btn-sm text-white flex-fill rounded-pill py-2 active mobile-staking-tab-btn" data-target="#activeStakesSection">
                <i class="las la-lock me-1"></i> @lang('Active') ({{ $activeStakes->count() }})
            </button>
            <button type="button" class="btn btn-sm text-muted flex-fill rounded-pill py-2 mobile-staking-tab-btn" data-target="#stakingPoolsSection">
                <i class="las la-cubes me-1"></i> @lang('Vaults')
            </button>
            <button type="button" class="btn btn-sm text-muted flex-fill rounded-pill py-2 mobile-staking-tab-btn" data-target="#stakeHistorySection">
                <i class="las la-history me-1"></i> @lang('History')
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Total Staked -->
        <div class="col-xl-3 col-6">
            <div class="staking-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Total Staked')</span>
                    <div class="staking-icon-badge bg-primary-soft text--base d-none d-sm-flex">
                        <i class="las la-wallet"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1 fs-5 fs-sm-4 font-mono">${{ number_format($statistics['total_staked'], 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between text--small">
                    <span class="text-muted">@lang('Spot Balance'): <strong class="text-white font-mono">${{ number_format($spotBalance, 2) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Total Earnings -->
        <div class="col-xl-3 col-6">
            <div class="staking-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Total Yield Earned')</span>
                    <div class="staking-icon-badge bg-success-soft text--success d-none d-sm-flex">
                        <i class="las la-chart-line"></i>
                    </div>
                </div>
                <h3 class="text--success fw-bold mb-1 fs-5 fs-sm-4 font-mono">+${{ number_format($statistics['total_earnings'], 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between text--small">
                    <span class="badge badge--success-soft rounded-pill px-2"><i class="las la-arrow-up"></i> @lang('Real-Time Yield')</span>
                </div>
            </div>
        </div>

        <!-- Active Stakes -->
        <div class="col-xl-3 col-6">
            <div class="staking-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Active Positions')</span>
                    <div class="staking-icon-badge bg-warning-soft text--warning d-none d-sm-flex">
                        <i class="las la-lock"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1 fs-5 fs-sm-4 font-mono">{{ $activeStakes->count() }}</h3>
                <div class="d-flex align-items-center text--small text-muted">
                    <span class="live-pulse-dot me-1"></span> @lang('Earning 24/7')
                </div>
            </div>
        </div>

        <!-- Best APY -->
        <div class="col-xl-3 col-6">
            <div class="staking-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Highest APY')</span>
                    <div class="staking-icon-badge bg-info-soft text--info d-none d-sm-flex">
                        <i class="las la-percentage"></i>
                    </div>
                </div>
                <h3 class="text--base fw-bold mb-1 fs-5 fs-sm-4 font-mono">{{ number_format($statistics['best_apy'] + $apyBoost, 2) }}%</h3>
                <div class="d-flex align-items-center text--small text-muted text-truncate">
                    <i class="las la-shield-alt text--success me-1"></i> @lang('Principal Protected')
                </div>
            </div>
        </div>
    </div>

    <!-- Active Stakes Section -->
    <div id="activeStakesSection" class="staking-content-section card bg--dark-two border-0 rounded-4 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4">
            <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                <i class="las la-lock text--base"></i> @lang('My Active Staking Positions')
                <span class="badge badge--primary rounded-pill">{{ $activeStakes->count() }}</span>
            </h5>
            <button type="button" class="btn btn-sm btn-outline--base rounded-pill px-3 openPoolsModalBtn">
                <i class="las la-plus"></i> @lang('Stake Crypto')
            </button>
        </div>
        <div class="card-body p-3 p-sm-4">
            @if($activeStakes->count() > 0)
                <div class="row g-3">
                    @foreach($activeStakes as $stake)
                        @php
                            $pool = $stake->pool;
                            $effectiveApy = ($pool ? $pool->apy_rate : 0) + $apyBoost;
                        @endphp
                        <div class="col-lg-6 col-xxl-4">
                            <div class="active-stake-card p-3 p-sm-4 rounded-4 position-relative overflow-hidden">
                                <div class="active-stake-glow"></div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="text-white fw-bold mb-1">{{ @$pool->name ?? 'Custom Stake' }}</h5>
                                        <div class="d-flex gap-2 align-items-center flex-wrap">
                                            <span class="badge badge--dark text-uppercase text--small">{{ @$pool->token_symbol ?? 'USDT' }}</span>
                                            <span class="badge badge--info-soft text--small font-mono">{{ $effectiveApy }}% APY</span>
                                            <span class="text-muted text--small font-mono"><i class="las la-calendar"></i> {{ $stake->start_time->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <span class="badge badge--success-soft rounded-pill px-3 py-1 d-flex align-items-center gap-1">
                                        <span class="live-pulse-dot"></span> @lang('EARNING')
                                    </span>
                                </div>

                                <div class="row g-2 mb-3 bg--dark-three p-3 rounded-3">
                                    <div class="col-6">
                                        <small class="text-muted text-uppercase d-block">@lang('Principal Staked')</small>
                                        <strong class="text-white fs-6 font-mono">${{ number_format($stake->principal_amount, 2) }}</strong>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted text-uppercase d-block">@lang('Accumulated Yield')</small>
                                        <strong class="text--success fs-6 font-mono">+${{ number_format($stake->live_rewards ?? $stake->accumulated_rewards, 2) }}</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted text-uppercase d-block">@lang('Term Duration')</small>
                                        <span class="text-white font-mono">{{ @$pool->lock_period_days > 0 ? @$pool->lock_period_days . ' Days' : 'Flexible' }}</span>
                                    </div>
                                    <div class="col-6 text-end mt-2">
                                        <small class="text-muted text-uppercase d-block">@lang('Maturity Date')</small>
                                        <span class="text-muted font-mono">{{ $stake->end_time ? $stake->end_time->format('M d, Y') : 'Anytime' }}</span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    @if(($stake->live_rewards ?? $stake->accumulated_rewards) > 0)
                                        <form action="{{ route('user.staking.harvest', $stake->id) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn--success w-100 rounded-pill py-2">
                                                <i class="las la-hand-holding-usd me-1"></i> @lang('Harvest Yield') (${{ number_format($stake->live_rewards ?? $stake->accumulated_rewards, 2) }})
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('user.staking.unstake', $stake->id) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline--danger w-100 rounded-pill py-2 confirmationBtn" data-question="@lang('Redeem & unstake your principal + earned rewards back to your Spot Wallet?')">
                                            <i class="las la-unlock me-1"></i> @lang('Unstake & Redeem')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-staking-icon mb-3">
                        <i class="las la-coins"></i>
                    </div>
                    <h5 class="text-white mb-2">@lang('No Active Staking Positions')</h5>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 460px;">
                        @lang('Deposit your crypto into high-yield staking vaults to earn guaranteed automated passive daily yields.')
                    </p>
                    <button type="button" class="btn btn--base rounded-pill px-4 py-2 openPoolsModalBtn">
                        <i class="las la-rocket me-1"></i> @lang('Explore Staking Vaults')
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Staking Vaults Marketplace (Desktop Full Grid) -->
    <div id="stakingPoolsSection" class="staking-content-section mb-5 pt-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="text-white fw-bold mb-1"><i class="las la-cubes text--base"></i> @lang('Available Staking Vaults')</h4>
                <p class="text-muted text--small mb-0">@lang('Select a staking pool to lock assets and receive automated yield distributions')</p>
            </div>
        </div>

        <div class="row g-4">
            @foreach($pools as $pool)
                @php
                    $poolEffectiveApy = $pool->apy_rate + $apyBoost;
                @endphp
                <div class="col-xl-3 col-md-6">
                    <div class="pool-plan-card h-100 p-4 rounded-4 d-flex flex-column justify-content-between position-relative">
                        @if($pool->badge_tag || $loop->first)
                            <div class="popular-ribbon">{{ $pool->badge_tag ?? 'HOT' }}</div>
                        @endif

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge--{{ $pool->type == 'flexible' ? 'info' : 'warning' }}-soft rounded-pill px-3 py-1 text-uppercase">
                                    {{ $pool->type }}
                                </span>
                                <span class="text-muted text--small font-mono"><i class="las la-history"></i> {{ $pool->lock_period_days > 0 ? $pool->lock_period_days . ' Days' : 'Flexible' }}</span>
                            </div>

                            <h4 class="text-white fw-bold mb-1">{{ __($pool->name) }}</h4>
                            <p class="text-muted text--small mb-3">{{ $pool->token_symbol }} Yield Vault</p>

                            <!-- APY Box -->
                            <div class="apy-highlight-box p-3 rounded-3 mb-3 text-center">
                                <span class="text-muted text--small text-uppercase d-block mb-1">@lang('Annual Percentage Yield')</span>
                                <h3 class="text--base fw-bold mb-0 font-mono">{{ number_format($poolEffectiveApy, 2) }}% APY</h3>
                                <small class="text-muted">@lang('Daily Rate'): <strong class="text--success font-mono">{{ number_format($poolEffectiveApy / 365, 4) }}%</strong></small>
                            </div>

                            <!-- Min / Max Limits -->
                            <div class="d-flex justify-content-between text--small mb-3 bg--dark-three p-2 rounded-2">
                                <span class="text-muted">@lang('Min'): <strong class="text-white font-mono">${{ number_format($pool->min_amount, 0) }}</strong></span>
                                <span class="text-muted">@lang('Max'): <strong class="text-white font-mono">${{ number_format($pool->max_amount, 0) }}</strong></span>
                            </div>

                            <div class="d-flex justify-content-between text--small text-muted mb-3 px-1">
                                <span>@lang('Total Staked'):</span>
                                <strong class="text-white font-mono">${{ number_format($pool->total_staked, 2) }}</strong>
                            </div>
                        </div>

                        <button type="button" class="btn btn--base w-100 rounded-pill py-2 fw-semibold stakeNowBtn"
                            data-id="{{ $pool->id }}"
                            data-name="{{ $pool->name }}"
                            data-token="{{ $pool->token_symbol }}"
                            data-apy="{{ $poolEffectiveApy }}"
                            data-days="{{ $pool->lock_period_days }}"
                            data-min="{{ $pool->min_amount }}"
                            data-max="{{ $pool->max_amount }}"
                            data-type="{{ $pool->type }}">
                            <i class="las la-lock me-1"></i> @lang('Stake Now')
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Stake History Table -->
    <div id="stakeHistorySection" class="staking-content-section card bg--dark-two border-0 rounded-4 shadow-sm">
        <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4">
            <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                <i class="las la-history text--base"></i> @lang('Staking Position Records')
            </h5>
            <span class="text-muted text--small font-mono">{{ $stakeHistory->count() }} @lang('Total Records')</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0 custom-staking-table">
                    <thead>
                        <tr>
                            <th class="ps-3 ps-sm-4">@lang('Start Date')</th>
                            <th>@lang('Vault Pool')</th>
                            <th>@lang('Asset')</th>
                            <th class="text-end">@lang('Principal')</th>
                            <th class="text-end">@lang('APY Rate')</th>
                            <th class="text-end">@lang('Total Yield')</th>
                            <th class="text-center">@lang('Term')</th>
                            <th class="text-center pe-3 pe-sm-4">@lang('Status')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stakeHistory as $stake)
                            <tr>
                                <td class="ps-3 ps-sm-4 text-nowrap font-mono">
                                    <span class="text-white fw-medium">{{ $stake->start_time->format('M d, Y') }}</span>
                                    <small class="text-muted d-block">{{ $stake->start_time->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="text-white fw-medium">{{ @$stake->pool->name ?? 'Staking Vault' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge--dark px-2 py-1 fw-bold font-mono">{{ @$stake->pool->token_symbol ?? 'USDT' }}</span>
                                </td>
                                <td class="text-end fw-medium text-white font-mono">${{ number_format($stake->principal_amount, 2) }}</td>
                                <td class="text-end fw-medium text--base font-mono">{{ @$stake->pool->apy_rate ?? 12.00 }}%</td>
                                <td class="text-end font-mono">
                                    <span class="text--success fw-bold">+${{ number_format($stake->accumulated_rewards, 2) }}</span>
                                </td>
                                <td class="text-center font-mono">
                                    {{ @$stake->pool->lock_period_days > 0 ? @$stake->pool->lock_period_days . 'D' : 'Flex' }}
                                </td>
                                <td class="text-center pe-3 pe-sm-4">
                                    @if($stake->status == 'active')
                                        <span class="badge badge--success-soft rounded-pill px-3 py-1 font-mono">
                                            <span class="live-pulse-dot me-1"></span> @lang('ACTIVE')
                                        </span>
                                    @else
                                        <span class="badge badge--dark rounded-pill px-3 py-1 font-mono text-muted">
                                            <i class="las la-check"></i> @lang('REDEEMED')
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="las la-lock-open fs-2 mb-2 d-block"></i>
                                    @lang('No staking records yet. Stake crypto in our yield vaults to begin earning.')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Staking Pools Selection Modal (For Mobile Quick Launch) -->
<div id="poolsMarketplaceModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content bg--dark-two border-0 rounded-4 text-white shadow-lg">
            <div class="modal-header border-bottom border-dark py-3 px-4">
                <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                    <i class="las la-cubes text--base"></i> @lang('Select Staking Vault')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-sm-4">
                <div class="row g-3">
                    @foreach($pools as $pool)
                        @php $modalApy = $pool->apy_rate + $apyBoost; @endphp
                        <div class="col-md-6">
                            <div class="pool-plan-card h-100 p-3 rounded-4 d-flex flex-column justify-content-between position-relative">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge badge--{{ $pool->type == 'flexible' ? 'info' : 'warning' }}-soft rounded-pill px-3 py-1 text-uppercase">
                                            {{ $pool->type }}
                                        </span>
                                        <span class="text-muted text--small font-mono">{{ $pool->lock_period_days > 0 ? $pool->lock_period_days . ' Days' : 'Flexible' }}</span>
                                    </div>
                                    <h5 class="text-white fw-bold mb-1">{{ __($pool->name) }}</h5>
                                    
                                    <div class="apy-highlight-box p-2 rounded-3 mb-2 text-center">
                                        <span class="text--base fw-bold fs-6 font-mono">{{ number_format($modalApy, 2) }}% APY</span>
                                        <small class="text-muted d-block font-mono">Daily: {{ number_format($modalApy / 365, 4) }}%</small>
                                    </div>

                                    <div class="d-flex justify-content-between text--small mb-3 bg--dark-three p-2 rounded-2">
                                        <span class="text-muted">Min: <strong class="text-white font-mono">${{ number_format($pool->min_amount, 0) }}</strong></span>
                                        <span class="text-muted">Max: <strong class="text-white font-mono">${{ number_format($pool->max_amount, 0) }}</strong></span>
                                    </div>
                                </div>

                                <button type="button" class="btn btn--base btn-sm w-100 rounded-pill py-2 fw-semibold stakeNowBtn"
                                    data-id="{{ $pool->id }}"
                                    data-name="{{ $pool->name }}"
                                    data-token="{{ $pool->token_symbol }}"
                                    data-apy="{{ $modalApy }}"
                                    data-days="{{ $pool->lock_period_days }}"
                                    data-min="{{ $pool->min_amount }}"
                                    data-max="{{ $pool->max_amount }}"
                                    data-type="{{ $pool->type }}">
                                    <i class="las la-lock me-1"></i> @lang('Select & Stake')
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stake Configuration Modal -->
<div id="stakeModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content bg--dark-two border-0 rounded-4 text-white shadow-lg">
            <div class="modal-header border-bottom border-dark py-3 px-4">
                <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                    <i class="las la-lock text--base"></i> <span id="modalPoolName">@lang('Stake in Vault')</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.staking.stake') }}" method="POST" id="stakeForm">
                @csrf
                <input type="hidden" name="pool_id" id="modalPoolId">
                <div class="modal-body p-4">
                    <!-- Expected Return Summary -->
                    <div class="bg--dark-three p-3 rounded-3 mb-4 text-center">
                        <span class="text-muted text--small text-uppercase d-block mb-1">@lang('Staking APY Return')</span>
                        <h3 class="text--base fw-bold mb-0 font-mono" id="modalApyText">18.50% APY</h3>
                        <small class="text-muted">@lang('Term Duration'): <strong class="text-white font-mono" id="modalDurationText">30</strong> @lang('Days')</small>
                    </div>

                    <!-- Wallet Selection with Custom Dark Styling -->
                    <div class="form-group mb-3">
                        <label class="form-label text-muted text--small text-uppercase">@lang('Funding Wallet Source')</label>
                        <select name="wallet_type" class="form-control form-select custom-dark-select" id="stakingWalletSelect" required>
                            <option value="spot" selected>Spot Wallet (${{ number_format($spotBalance, 2) }} USDT)</option>
                            <option value="funding">Funding Wallet (${{ number_format($fundingBalance, 2) }} USDT)</option>
                        </select>
                    </div>

                    <!-- Capital Allocation Amount -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-muted text--small text-uppercase mb-0">@lang('Stake Amount ($)')</label>
                            <span class="text-muted text--small">@lang('Limit'): <span id="modalLimitsText" class="text--base font-mono"></span></span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg--dark-three text-white border-dark">$</span>
                            <input type="number" step="any" name="amount" class="form-control bg--dark-three text-white border-dark fs-5 font-mono" id="stakeAmountInput" placeholder="0.00" required>
                            <span class="input-group-text bg--dark-three text-white border-dark">USDT</span>
                        </div>

                        <!-- Quick Percentage Pills -->
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-stake-pct-btn font-mono" data-pct="25">25%</button>
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-stake-pct-btn font-mono" data-pct="50">50%</button>
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-stake-pct-btn font-mono" data-pct="75">75%</button>
                            <button type="button" class="btn btn-sm btn-outline--base flex-fill rounded-pill quick-stake-pct-btn font-mono" data-pct="100">MAX</button>
                        </div>
                    </div>

                    <!-- Projected Returns Breakdown -->
                    <div class="bg--dark-three p-3 rounded-3 mb-2">
                        <div class="d-flex justify-content-between text--small mb-1">
                            <span class="text-muted">@lang('Est. Daily Yield'):</span>
                            <strong class="text--success font-mono" id="estDailyYield">+$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between text--small">
                            <span class="text-muted">@lang('Est. Total Return'):</span>
                            <strong class="text-white font-mono" id="estTotalYield">+$0.00</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-dark p-4">
                    <button type="submit" class="btn btn--base w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm">
                        <i class="las la-lock me-1"></i> @lang('Confirm & Lock Stake')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('style')
<style>
    .staking-terminal-wrapper {
        color: #e2e8f0;
        max-width: 100%;
        overflow-x: clip;
    }
    .font-mono {
        font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', Courier, monospace !important;
    }
    .bg--dark-two {
        background: #0f172a !important;
    }
    .bg--dark-three {
        background: #1e293b !important;
    }
    .staking-mobile-nav {
        border: 1px solid #334155;
    }
    .staking-mobile-nav .btn.active {
        background: #3b82f6 !important;
        color: #fff !important;
        font-weight: 600;
    }
    .live-pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px #10b981;
        animation: pulseAnimation 1.5s infinite;
    }
    @keyframes pulseAnimation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .staking-metric-card {
        background: #0f172a;
        border: 1px solid #1e293b;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .staking-metric-card:hover {
        transform: translateY(-2px);
        border-color: #3b82f6;
    }
    .staking-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .bg-primary-soft { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .bg-info-soft { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge--success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge--danger-soft { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .badge--warning-soft { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge--info-soft { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }
    
    .active-stake-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid #334155;
    }
    .pool-plan-card {
        background: #0f172a;
        border: 1px solid #1e293b;
        transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .pool-plan-card:hover {
        transform: translateY(-4px);
        border-color: #3b82f6;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.2);
    }
    .popular-ribbon {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #3b82f6;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }
    .apy-highlight-box {
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    .empty-staking-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
    }
    .custom-staking-table th {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
        font-size: 11px;
        text-transform: uppercase;
        border: none;
    }
    .custom-staking-table td {
        border-bottom: 1px solid #1e293b !important;
        padding: 10px 8px;
        font-size: 13px;
    }

    /* Custom Dark Dropdown Styling */
    .custom-dark-select,
    select.custom-dark-select {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border: 1px solid #475569 !important;
        border-radius: 10px !important;
        padding: 12px 16px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%233b82f6' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 1rem center !important;
        background-size: 16px 12px !important;
    }
    .custom-dark-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25) !important;
    }
    .custom-dark-select option {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        padding: 12px !important;
    }

    /* Hide the topbar/sidebar hamburger menu button on mobile so back button is exclusively used */
    @media (max-width: 1199px) {
        .dashboard-body__bar,
        .dashboard-sidebar-filter__button,
        .dashboardBodyNav {
            display: none !important;
        }
    }
</style>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";

        var currentSpotBalance = parseFloat("{{ $spotBalance }}");
        var currentFundingBalance = parseFloat("{{ $fundingBalance }}");
        var selectedPoolMin = 0;
        var selectedPoolMax = 0;
        var selectedPoolApy = 0;
        var selectedPoolDays = 0;

        // Open Pools Marketplace Modal
        $('.openPoolsModalBtn').on('click', function () {
            $('#poolsMarketplaceModal').modal('show');
        });

        // Launch configure modal from pool card
        $('.stakeNowBtn').on('click', function () {
            $('#poolsMarketplaceModal').modal('hide');
            var modal = $('#stakeModal');
            var poolId = $(this).data('id');
            var name = $(this).data('name');
            selectedPoolApy = parseFloat($(this).data('apy'));
            selectedPoolDays = parseInt($(this).data('days')) || 30;
            selectedPoolMin = parseFloat($(this).data('min')) || 0;
            selectedPoolMax = parseFloat($(this).data('max')) || 0;
            var type = $(this).data('type');

            modal.find('#modalPoolId').val(poolId);
            modal.find('#modalPoolName').text(name);
            modal.find('#modalApyText').text(selectedPoolApy.toFixed(2) + '% APY');
            modal.find('#modalDurationText').text(selectedPoolDays > 0 ? selectedPoolDays : 'Flexible');
            modal.find('#modalLimitsText').text('$' + selectedPoolMin.toLocaleString() + ' - $' + selectedPoolMax.toLocaleString());
            modal.find('#stakeAmountInput').val(selectedPoolMin > 0 ? selectedPoolMin : 100);
            
            calculateEstimatedYield(selectedPoolMin > 0 ? selectedPoolMin : 100);
            modal.modal('show');
        });

        $('#stakeAmountInput').on('input', function () {
            var amount = parseFloat($(this).val()) || 0;
            calculateEstimatedYield(amount);
        });

        $('.quick-stake-pct-btn').on('click', function () {
            var pct = parseFloat($(this).data('pct'));
            var walletType = $('#stakingWalletSelect').val();
            var balance = (walletType === 'spot') ? currentSpotBalance : currentFundingBalance;
            var calculatedAmount = (balance * (pct / 100));

            if (selectedPoolMax && calculatedAmount > selectedPoolMax) {
                calculatedAmount = selectedPoolMax;
            }

            $('#stakeAmountInput').val(calculatedAmount.toFixed(2));
            calculateEstimatedYield(calculatedAmount);
        });

        function calculateEstimatedYield(amount) {
            if (amount > 0 && selectedPoolApy > 0) {
                var dailyYield = (amount * (selectedPoolApy / 100) / 365);
                var totalYield = dailyYield * (selectedPoolDays > 0 ? selectedPoolDays : 365);
                $('#estDailyYield').text('+$' + dailyYield.toFixed(4) + ' / day');
                $('#estTotalYield').text('+$' + totalYield.toFixed(2) + ' (' + (selectedPoolDays > 0 ? selectedPoolDays + ' Days' : '1 Year') + ')');
            } else {
                $('#estDailyYield').text('+$0.00');
                $('#estTotalYield').text('+$0.00');
            }
        }

        // Mobile Tabs Switcher - Exclusive visibility on mobile screens
        function handleMobileStakingTabs() {
            if ($(window).width() < 768) {
                var activeTarget = $('.mobile-staking-tab-btn.active').data('target') || '#activeStakesSection';
                $('.staking-content-section').addClass('d-none');
                $(activeTarget).removeClass('d-none');
            } else {
                $('.staking-content-section').removeClass('d-none');
            }
        }

        handleMobileStakingTabs();
        $(window).on('resize', handleMobileStakingTabs);

        $('.mobile-staking-tab-btn').on('click', function() {
            $('.mobile-staking-tab-btn').removeClass('active text-white').addClass('text-muted');
            $(this).addClass('active text-white').removeClass('text-muted');

            var target = $(this).data('target');
            $('.staking-content-section').addClass('d-none');
            $(target).removeClass('d-none');
        });
    })(jQuery);
</script>
@endpush