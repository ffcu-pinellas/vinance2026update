@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="ai-terminal-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1" id="soundToggleBtn">
                <i class="las la-volume-up text--info" id="soundToggleIcon"></i> <span id="soundToggleText">@lang('Audio')</span>
            </button>
            <span class="badge badge--success-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
                <span class="live-pulse-dot"></span> @lang('AI Engine Online')
            </span>
        </div>
    </div>

    <!-- Main Header Card -->
    <div class="ai-header-card bg--dark-two p-3 p-md-4 rounded-4 mb-3 border-0 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h3 class="text-white fw-bold mb-1 fs-4 d-flex align-items-center gap-2">
                    <i class="las la-robot text--base"></i> @lang('AI Auto-Trader')
                </h3>
                <p class="text-muted text--small mb-0">@lang('Automated 24/7 high-frequency quant algorithmic trading')</p>
            </div>

            <!-- Balances & Deploy Action -->
            <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-between justify-content-md-end flex-wrap">
                <div class="user-wallet-pill bg--dark-three border border-dark rounded-pill px-3 py-2 d-flex align-items-center gap-2 text--small">
                    <span class="text-muted"><i class="las la-wallet text--base"></i> Spot: <strong class="text-white font-mono">${{ number_format($spotBalance, 2) }}</strong></span>
                    <span class="text-muted opacity-50">|</span>
                    <span class="text-muted"><i class="las la-coins text--info"></i> Fund: <strong class="text-white font-mono">${{ number_format($fundingBalance, 2) }}</strong></span>
                </div>

                <button type="button" class="btn btn--base btn-sm rounded-pill px-3 py-2 text-nowrap openMarketplaceBtn">
                    <i class="las la-plus-circle me-1"></i> @lang('Deploy Bot')
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile View Tab Switcher (Visible only on Mobile screens) -->
    <div class="d-md-none mb-3">
        <div class="ai-mobile-nav p-1 rounded-pill bg--dark-two d-flex shadow-sm gap-1">
            <button type="button" class="btn btn-sm text-white flex-fill rounded-pill py-2 active mobile-tab-btn" data-target="#activeBotsSection">
                <i class="las la-robot me-1"></i> @lang('My Bots') ({{ $activeBots->count() }})
            </button>
            <button type="button" class="btn btn-sm text-muted flex-fill rounded-pill py-2 mobile-tab-btn" data-target="#copyTradingSection">
                <i class="las la-trophy me-1"></i> @lang('Copy')
            </button>
            <button type="button" class="btn btn-sm text-muted flex-fill rounded-pill py-2 mobile-tab-btn" data-target="#liveSignalsSection">
                <i class="las la-bolt me-1"></i> @lang('Signals')
            </button>
            <button type="button" class="btn btn-sm text-muted flex-fill rounded-pill py-2 mobile-tab-btn" data-target="#tradeHistorySection">
                <i class="las la-history me-1"></i> @lang('History')
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Active Capital -->
        <div class="col-xl-3 col-6">
            <div class="ai-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Active Capital')</span>
                    <div class="ai-icon-badge bg-primary-soft text--base d-none d-sm-flex">
                        <i class="las la-coins"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1 fs-5 fs-sm-4 font-mono">${{ number_format($totalAllocated, 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between text--small">
                    <span class="text-muted">@lang('Spot'): <strong class="text-white font-mono">${{ number_format($spotBalance, 2) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Realized Profit -->
        <div class="col-xl-3 col-6">
            <div class="ai-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Total Profit')</span>
                    <div class="ai-icon-badge bg-success-soft text--success d-none d-sm-flex">
                        <i class="las la-chart-line"></i>
                    </div>
                </div>
                <h3 class="text--success fw-bold mb-1 fs-5 fs-sm-4 font-mono">+${{ number_format($totalProfit, 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between text--small">
                    <span class="badge badge--success-soft rounded-pill px-2"><i class="las la-arrow-up"></i> @lang('Net Return')</span>
                </div>
            </div>
        </div>

        <!-- Win Rate -->
        <div class="col-xl-3 col-6">
            <div class="ai-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Win Rate')</span>
                    <div class="ai-icon-badge bg-info-soft text--info d-none d-sm-flex">
                        <i class="las la-shield-alt"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1 fs-5 fs-sm-4 font-mono">{{ number_format($winRate, 1) }}%</h3>
                <div class="d-flex align-items-center text--small text-muted text-truncate">
                    <i class="las la-check-circle text--success me-1"></i> @lang('AI Verified')
                </div>
            </div>
        </div>

        <!-- Active Bots -->
        <div class="col-xl-3 col-6">
            <div class="ai-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Running Bots')</span>
                    <div class="ai-icon-badge bg-warning-soft text--warning d-none d-sm-flex">
                        <i class="las la-microchip"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1 fs-5 fs-sm-4 font-mono">{{ $activeBots->count() }} <span class="text-muted fs-6 fw-normal">/ {{ $plans->count() }}</span></h3>
                <div class="d-flex align-items-center text--small text-muted">
                    <span class="live-pulse-dot me-1"></span> {{ $totalTrades }} @lang('Trades')
                </div>
            </div>
        </div>
    </div>

    <!-- Active Bots Section -->
    <div id="activeBotsSection" class="ai-content-section card bg--dark-two border-0 rounded-4 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4 flex-wrap gap-2">
            <h5 class="text-white mb-0 d-flex align-items-center gap-2 text-nowrap">
                <i class="las la-server text--base"></i> <span>@lang('My Active Bots')</span>
                <span class="badge badge--primary rounded-pill">{{ $activeBots->count() }}</span>
            </h5>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($activeBots->sum('current_profit') > 0 && $activeBots->count() > 1)
                    <form action="{{ route('user.ai.bot.harvest.all') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline--success rounded-pill px-3 text-nowrap">
                            <i class="las la-hand-holding-usd me-1"></i> @lang('Harvest All') (${{ number_format($activeBots->sum('current_profit'), 2) }})
                        </button>
                    </form>
                @endif
                <button type="button" class="btn btn-sm btn-outline--base rounded-pill px-3 openMarketplaceBtn text-nowrap">
                    <i class="las la-plus"></i> @lang('Deploy Bot')
                </button>
            </div>
        </div>
        <div class="card-body p-3 p-sm-4">
            @if($activeBots->count() > 0)
                <div class="row g-3">
                    @foreach($activeBots as $userBot)
                        <div class="col-lg-6 col-xxl-4 {{ $loop->iteration > 4 ? 'extended-active-bot d-none' : '' }}">
                            <div class="active-bot-card p-3 p-sm-4 rounded-4 position-relative overflow-hidden">
                                <div class="active-bot-glow"></div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="text-white fw-bold mb-1">{{ @$userBot->plan->name }}</h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge badge--dark text-uppercase text--small">{{ @$userBot->plan->strategy_type }}</span>
                                            <span class="text-muted text--small font-mono"><i class="las la-clock"></i> <span class="bot-uptime" data-start="{{ $userBot->created_at->toISOString() }}">0h 0m</span></span>
                                        </div>
                                    </div>
                                    <span class="badge badge--success-soft rounded-pill px-3 py-1 d-flex align-items-center gap-1 text-nowrap">
                                        <span class="live-pulse-dot"></span> @lang('RUNNING')
                                    </span>
                                </div>

                                <div class="row g-2 mb-3 bg--dark-three p-3 rounded-3">
                                    <div class="col-6">
                                        <small class="text-muted text-uppercase d-block">@lang('Capital')</small>
                                        <strong class="text-white fs-6 font-mono">${{ number_format($userBot->allocated_amount, 2) }}</strong>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted text-uppercase d-block">@lang('Profit')</small>
                                        <strong class="text--success fs-6 font-mono">+${{ number_format($userBot->current_profit, 2) }}</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted text-uppercase d-block">@lang('Daily ROI')</small>
                                        <span class="text--base fw-semibold font-mono">{{ @$userBot->plan->daily_roi_min }}% - {{ @$userBot->plan->daily_roi_max }}%</span>
                                    </div>
                                    <div class="col-6 text-end mt-2">
                                        <small class="text-muted text-uppercase d-block">@lang('Trades')</small>
                                        <span class="text-white font-mono">{{ $userBot->total_trades }} @lang('orders')</span>
                                    </div>
                                </div>

                                <!-- Auto-Compound Toggle -->
                                <div class="d-flex align-items-center justify-content-between p-2 px-3 rounded-3 bg--dark-three border border-dark mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="las la-sync-alt text--info fs-5"></i>
                                        <div>
                                            <span class="text-white text--small fw-bold d-block" style="font-size: 12px;">@lang('Auto-Compound Daily Yield')</span>
                                            <small class="text-muted" style="font-size: 10px;">@lang('Reinvests profits into working capital')</small>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch form-switch-success m-0">
                                        <input class="form-check-input bot-autocompound-toggle" type="checkbox" role="switch" data-id="{{ $userBot->id }}" {{ $userBot->auto_compound ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    @if($userBot->current_profit > 0)
                                        <div class="col-6">
                                            <form action="{{ route('user.ai.bot.harvest', $userBot->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn--success w-100 rounded-pill py-2 text-nowrap px-1" style="font-size: 11.5px;">
                                                    <i class="las la-hand-holding-usd me-1"></i> @lang('Harvest') (${{ number_format($userBot->current_profit, 2) }})
                                                </button>
                                            </form>
                                        </div>
                                        <div class="col-6">
                                            <form action="{{ route('user.ai.bot.stop', $userBot->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger w-100 rounded-pill py-2 confirmationBtn text-nowrap px-1" style="font-size: 11.5px;" data-question="@lang('Stop this bot and return allocated capital + all accumulated profits to your Spot Wallet?')">
                                                    <i class="las la-stop-circle me-1"></i> @lang('Pause & Refund')
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <form action="{{ route('user.ai.bot.stop', $userBot->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger w-100 rounded-pill py-2 confirmationBtn text-nowrap" style="font-size: 12px;" data-question="@lang('Stop this bot and return allocated capital + all accumulated profits to your Spot Wallet?')">
                                                    <i class="las la-stop-circle me-1"></i> @lang('Pause & Refund')
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($activeBots->count() > 4)
                    <div class="text-center pt-3 mt-2 border-top border-dark">
                        <button type="button" class="btn btn-sm btn-outline--light rounded-pill px-4" id="toggleExtendedActiveBotsBtn">
                            <i class="las la-angle-down me-1"></i> <span id="toggleExtendedActiveBotsText">@lang('Show All') {{ $activeBots->count() }} @lang('Active Bots')</span>
                        </button>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="empty-ai-icon mb-3">
                        <i class="las la-robot"></i>
                    </div>
                    <h5 class="text-white mb-2">@lang('No AI Trading Bots Currently Deployed')</h5>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 460px;">
                        @lang('Select one of our institutional strategies to begin automated 24/7 high-frequency quantitative trading.')
                    </p>
                    <button type="button" class="btn btn--base rounded-pill px-4 py-2 openMarketplaceBtn">
                        <i class="las la-rocket me-1"></i> @lang('Deploy AI Bot')
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- INSTITUTIONAL COPY TRADING LEADERBOARD -->
    <div id="copyTradingSection" class="ai-content-section card bg--dark-two border-0 rounded-4 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4 flex-wrap gap-2">
            <div>
                <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                    <i class="las la-trophy text--warning"></i> @lang('Institutional Copy Trading Leaderboard')
                </h5>
                <small class="text-muted">@lang('1-Click copy verified institutional algorithmic strategies calibrated for maximum alpha')</small>
            </div>
            <span class="badge badge--success-soft rounded-pill px-3 py-1 font-mono">
                <span class="live-pulse-dot me-1"></span> 5 Top Performers
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0 custom-trades-table">
                    <thead>
                        <tr>
                            <th class="ps-3 ps-sm-4">@lang('Strategy & Profile')</th>
                            <th>@lang('Type')</th>
                            <th class="text-end">@lang('30D Return')</th>
                            <th class="text-end">@lang('Win Rate')</th>
                            <th class="text-center">@lang('Risk Level')</th>
                            <th class="text-end pe-3 pe-sm-4">@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($copyTradingBots as $bot)
                            @php
                                $botInitials = strtoupper(substr($bot->name, 0, 2));
                                $avgRoi = round(($bot->daily_roi_min + $bot->daily_roi_max) / 2 * 30, 1);
                                $palette = ['primary', 'success', 'info', 'warning', 'danger'];
                                $badgeColor = $palette[$loop->index % count($palette)];
                            @endphp
                            <tr class="{{ $loop->iteration > 5 ? 'extended-bot-row d-none' : '' }}">
                                <td class="ps-3 ps-sm-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="institution-avatar-badge bg-{{ $badgeColor }}-soft text--{{ $badgeColor == 'primary' ? 'base' : $badgeColor }} fw-bold font-mono">
                                            {{ $bot->avatar_code ?? $botInitials }}
                                        </div>
                                        <div>
                                            <span class="text-white fw-bold d-block">{{ __($bot->name) }}</span>
                                            <small class="text-muted">{{ __($bot->tagline) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge--dark text-uppercase font-mono">{{ $bot->strategy_type }}</span></td>
                                <td class="text-end font-mono text--success fw-bold live-return-val" data-base="{{ $avgRoi }}">+{{ $avgRoi }}%</td>
                                <td class="text-end font-mono text-white">{{ $bot->win_rate }}%</td>
                                <td class="text-center">
                                    <span class="badge badge--{{ $bot->risk_level == 'low' ? 'success' : ($bot->risk_level == 'medium' ? 'warning' : 'danger') }}-soft rounded-pill px-2">
                                        {{ ucfirst($bot->risk_level) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 pe-sm-4">
                                    <button type="button" class="btn btn-sm btn--base rounded-pill px-3 deployBotBtn"
                                        data-id="{{ $bot->id }}"
                                        data-name="{{ $bot->name }}"
                                        data-min="{{ $bot->min_investment }}"
                                        data-max="{{ $bot->max_investment }}"
                                        data-roi_min="{{ $bot->daily_roi_min }}"
                                        data-roi_max="{{ $bot->daily_roi_max }}"
                                        data-duration="{{ $bot->trade_duration_days }}">
                                        <i class="las la-copy me-1"></i> @lang('Copy Strategy')
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">@lang('No copy trading bots available.')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- See More / Show Less Toggle Button -->
        @if($copyTradingBots->count() > 5)
        <div class="card-footer bg-transparent border-top border-dark text-center py-3">
            <button type="button" class="btn btn-sm btn-outline--light rounded-pill px-4" id="toggleExtendedBotsBtn">
                <i class="las la-angle-down me-1"></i> <span id="toggleExtendedBotsText">@lang('View All') {{ $copyTradingBots->count() }} @lang('Institutional Strategies')</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Strategy Marketplace (Desktop Full Grid) -->
    <div id="desktopMarketplace" class="d-none d-md-block mb-5 pt-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="text-white fw-bold mb-1"><i class="las la-brain text--base"></i> @lang('AI Strategies')</h4>
                <p class="text-muted text--small mb-0">@lang('Neural algorithms calibrated for optimal risk-adjusted alpha')</p>
            </div>
        </div>

        <div class="row g-4">
            @foreach($plans as $plan)
                <div class="col-md-6 col-xl-4">
                    <div class="bot-plan-card h-100 p-4 rounded-4 d-flex flex-column justify-content-between position-relative overflow-hidden">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge badge--{{ $plan->risk_level == 'low' ? 'success' : ($plan->risk_level == 'medium' ? 'warning' : 'danger') }}-soft rounded-pill px-3 py-1 text-uppercase">
                                    {{ $plan->risk_level }} @lang('Risk')
                                </span>
                                <span class="text-muted text--small font-mono"><i class="las la-history"></i> {{ $plan->trade_duration_days }} @lang('Days')</span>
                            </div>

                            <h4 class="text-white fw-bold mb-1">{{ __($plan->name) }}</h4>
                            <p class="text-muted text--small mb-3">{{ __($plan->tagline) }}</p>

                            <!-- Daily ROI Highlight Box -->
                            <div class="roi-highlight-box p-3 rounded-3 mb-3 text-center">
                                <span class="text-muted text--small text-uppercase d-block mb-1">@lang('Target Daily ROI')</span>
                                <h3 class="text--base fw-bold mb-0 font-mono">{{ $plan->daily_roi_min }}% - {{ $plan->daily_roi_max }}%</h3>
                                <small class="text-muted">Win Rate: <strong class="text--success font-mono">{{ $plan->win_rate }}%</strong></small>
                            </div>

                            <div class="investment-limits-box bg--dark-three p-3 rounded-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted text--small">@lang('Min Capital'):</span>
                                    <span class="text-white font-mono fw-semibold">${{ number_format($plan->min_investment, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted text--small">@lang('Max Capital'):</span>
                                    <span class="text-white font-mono fw-semibold">${{ number_format($plan->max_investment, 2) }}</span>
                                </div>
                            </div>

                            <ul class="feature-list list-unstyled mb-4 text--small">
                                @if($plan->features)
                                    @foreach($plan->features as $feature)
                                        <li class="d-flex align-items-start gap-2 mb-2">
                                            <i class="las la-check-circle text--success mt-1"></i>
                                            <span class="text-light">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        <button type="button" class="btn btn--base w-100 rounded-pill py-2 fw-semibold deployBotBtn"
                            data-id="{{ $plan->id }}"
                            data-name="{{ $plan->name }}"
                            data-min="{{ $plan->min_investment }}"
                            data-max="{{ $plan->max_investment }}"
                            data-roi_min="{{ $plan->daily_roi_min }}"
                            data-roi_max="{{ $plan->daily_roi_max }}"
                            data-duration="{{ $plan->trade_duration_days }}">
                            <i class="las la-play me-1"></i> @lang('Deploy Bot')
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Live AI Signal Feed -->
    <div id="liveSignalsSection" class="ai-content-section mb-4">
        <div class="card bg--dark-two border-0 rounded-4 shadow-sm">
            <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4">
                <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                    <i class="las la-bolt text--base"></i> @lang('Live AI Signals')
                    <span class="badge badge--success-soft rounded-pill text--small"><span class="live-pulse-dot"></span> @lang('LIVE')</span>
                </h5>
                <small class="text-muted font-mono">1.1ms latency</small>
            </div>
            <div class="card-body p-3">
                <div class="ai-signal-terminal p-3 rounded-3" id="signalTerminal">
                    <div class="signal-line text--small mb-2">
                        <span class="text-muted">[{{ date('H:i:s') }}]</span> <span class="text--base fw-bold">[QUANT-SCAN]</span> Scanning 48 pairs via WebSocket feed...
                    </div>
                    <div class="signal-line text--small mb-2">
                        <span class="text-muted">[{{ date('H:i:s', time()-6) }}]</span> <span class="text--success fw-bold">[SIGNAL-BUY]</span> <strong>BTC/USDT</strong> (@ $77,901.50) Momentum Breakout &rarr; Consensus: 98.6%
                    </div>
                    <div class="signal-line text--small mb-2">
                        <span class="text-muted">[{{ date('H:i:s', time()-18) }}]</span> <span class="text--info fw-bold">[ARBITRAGE]</span> <strong>SOL/USDT</strong> (@ $195.40) Spread +0.42% captured
                    </div>
                    <div class="signal-line text--small mb-2">
                        <span class="text-muted">[{{ date('H:i:s', time()-32) }}]</span> <span class="text--success fw-bold">[TAKE-PROFIT]</span> <strong>ETH/USDT</strong> (@ $3,120.80) Target 2 cleared (+3.12%)
                    </div>
                    <div class="signal-line text--small mb-2">
                        <span class="text-muted">[{{ date('H:i:s', time()-47) }}]</span> <span class="text--warning fw-bold">[DEPTH-SWEEP]</span> <strong>XRP/USDT</strong> (@ $0.5840) Bid wall absorbed
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trade Execution Logs Table (Paginated 5-at-a-time) -->
    <div id="tradeHistorySection" class="ai-content-section card bg--dark-two border-0 rounded-4 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4">
            <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                <i class="las la-history text--base"></i> @lang('Trade History')
            </h5>
            <span class="text-muted text--small font-mono">{{ $tradeLogs->count() }} @lang('Total')</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0 custom-ai-table">
                    <thead>
                        <tr>
                            <th class="ps-3 ps-sm-4">@lang('Date / Time')</th>
                            <th>@lang('Pair')</th>
                            <th>@lang('Side')</th>
                            <th class="text-end">@lang('Entry')</th>
                            <th class="text-end">@lang('Exit')</th>
                            <th class="text-end">@lang('Volume')</th>
                            <th class="text-end">@lang('Profit')</th>
                            <th class="text-center pe-3 pe-sm-4">@lang('Status')</th>
                        </tr>
                    </thead>
                    <tbody id="tradeHistoryTableBody">
                        @forelse($tradeLogs as $trade)
                            <tr class="trade-log-row {{ $loop->iteration > 5 ? 'd-none' : '' }}" data-index="{{ $loop->iteration }}">
                                <td class="ps-3 ps-sm-4 text-nowrap font-mono">
                                    <span class="text-white fw-medium">{{ showDateTime($trade->created_at, 'M d, Y') }}</span>
                                    <small class="text-muted d-block">{{ showDateTime($trade->created_at, 'H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge badge--dark px-2 py-1 fw-bold font-mono">{{ $trade->pair_symbol }}</span>
                                </td>
                                <td>
                                    <span class="badge badge--{{ $trade->side == 'BUY' ? 'success' : 'danger' }}-soft px-3 py-1 font-mono">
                                        {{ $trade->side }}
                                    </span>
                                </td>
                                <td class="text-end fw-medium text-white font-mono">${{ number_format($trade->entry_price, 2) }}</td>
                                <td class="text-end fw-medium text-white font-mono">${{ number_format($trade->exit_price, 2) }}</td>
                                <td class="text-end fw-medium text-white font-mono">${{ number_format($trade->amount, 2) }}</td>
                                <td class="text-end font-mono">
                                    <span class="{{ $trade->profit_amount >= 0 ? 'text--success' : 'text--danger' }} fw-bold">
                                        {{ $trade->profit_amount >= 0 ? '+' : '' }}${{ number_format($trade->profit_amount, 2) }}
                                    </span>
                                    <small class="text-muted d-block">({{ $trade->profit_percentage >= 0 ? '+' : '' }}{{ $trade->profit_percentage }}%)</small>
                                </td>
                                <td class="text-center pe-3 pe-sm-4">
                                    @if($trade->status == 'open')
                                        <span class="badge badge--warning-soft rounded-pill px-3 py-1 font-mono">
                                            <span class="live-pulse-dot me-1"></span> @lang('HOLDING')
                                        </span>
                                    @else
                                        <span class="badge badge--success-soft rounded-pill px-3 py-1 font-mono">
                                            <i class="las la-check"></i> @lang('CLOSED')
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="las la-exchange-alt fs-2 mb-2 d-block"></i>
                                    @lang('No trade records yet. Deploy an AI bot above to begin receiving automated executions.')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tradeLogs->count() > 5)
            <div class="card-footer bg-transparent border-top border-dark text-center py-3">
                <button type="button" class="btn btn-sm btn-outline--light rounded-pill px-4" id="loadMoreTradesBtn">
                    <i class="las la-angle-down me-1"></i> <span id="loadMoreTradesText">@lang('Show 5 More Trades')</span>
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Strategy Selection Modal (For Mobile & Quick Launch) -->
<div id="marketplaceModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content bg--dark-two border-0 rounded-4 text-white shadow-lg">
            <div class="modal-header border-bottom border-dark py-3 px-4">
                <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                    <i class="las la-brain text--base"></i> @lang('Select Strategy')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-sm-4">
                <div class="row g-3">
                    @foreach($plans as $plan)
                        <div class="col-md-6">
                            <div class="bot-plan-card h-100 p-3 rounded-4 d-flex flex-column justify-content-between position-relative">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge badge--{{ $plan->risk_level == 'low' ? 'success' : ($plan->risk_level == 'medium' ? 'warning' : 'danger') }}-soft rounded-pill px-3 py-1 text-uppercase">
                                            {{ $plan->risk_level }} @lang('Risk')
                                        </span>
                                        <span class="text-muted text--small"><i class="las la-history"></i> {{ $plan->trade_duration_days }} @lang('Days')</span>
                                    </div>
                                    <h5 class="text-white fw-bold mb-1">{{ __($plan->name) }}</h5>
                                    <p class="text-muted text--small mb-2">{{ __($plan->tagline) }}</p>
                                    
                                    <div class="roi-highlight-box p-2 rounded-3 mb-2 text-center">
                                        <span class="text--base fw-bold fs-6 font-mono">{{ $plan->daily_roi_min }}% - {{ $plan->daily_roi_max }}% / Day</span>
                                        <small class="text-muted d-block">Win Rate: <strong class="text--success font-mono">{{ $plan->win_rate }}%</strong></small>
                                    </div>

                                    <div class="d-flex justify-content-between text--small mb-3 bg--dark-three p-2 rounded-2">
                                        <span class="text-muted">Min: <strong class="text-white font-mono">${{ number_format($plan->min_investment, 0) }}</strong></span>
                                        <span class="text-muted">Max: <strong class="text-white font-mono">${{ number_format($plan->max_investment, 0) }}</strong></span>
                                    </div>
                                </div>

                                <button type="button" class="btn btn--base btn-sm w-100 rounded-pill py-2 fw-semibold selectAndDeployBtn"
                                    data-id="{{ $plan->id }}"
                                    data-name="{{ $plan->name }}"
                                    data-min="{{ $plan->min_investment }}"
                                    data-max="{{ $plan->max_investment }}"
                                    data-roi_min="{{ $plan->daily_roi_min }}"
                                    data-roi_max="{{ $plan->daily_roi_max }}"
                                    data-duration="{{ $plan->trade_duration_days }}">
                                    <i class="las la-play me-1"></i> @lang('Select & Configure')
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deploy Bot Configuration Modal -->
<div id="deployBotModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content bg--dark-two border-0 rounded-4 text-white shadow-lg">
            <div class="modal-header border-bottom border-dark py-3 px-4">
                <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                    <i class="las la-rocket text--base"></i> <span id="modalBotName">@lang('Deploy AI Bot')</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.ai.bot.start') }}" method="POST" id="deployForm">
                @csrf
                <input type="hidden" name="plan_id" id="modalPlanId">
                <div class="modal-body p-4">
                    <!-- Expected Return Summary -->
                    <div class="bg--dark-three p-3 rounded-3 mb-4 text-center">
                        <span class="text-muted text--small text-uppercase d-block mb-1">@lang('Daily ROI')</span>
                        <h3 class="text--base fw-bold mb-0 font-mono" id="modalRoiText">1.50% - 3.20%</h3>
                        <small class="text-muted">@lang('Duration'): <strong class="text-white font-mono" id="modalDurationText">30</strong> @lang('Days')</small>
                    </div>

                    <!-- Wallet Selection with Interactive Card Pills -->
                    <div class="form-group mb-3">
                        <label class="form-label text-muted text--small text-uppercase mb-2">@lang('Wallet Source')</label>
                        <div class="row g-2" id="aiWalletTypePillsContainer">
                            <div class="col-6">
                                <div class="wallet-type-pill p-2 rounded-3 border border-dark bg--dark-three cursor-pointer d-flex align-items-center gap-2 active border--base" data-val="spot" style="cursor: pointer; transition: all 0.2s ease;">
                                    <div class="wallet-icon-box text--base fs-4">
                                        <i class="las la-chart-line"></i>
                                    </div>
                                    <div>
                                        <strong class="text-white d-block text--small font-mono">@lang('Spot Wallet')</strong>
                                        <small class="text-muted font-mono" style="font-size: 11px;">${{ number_format($spotBalance, 2) }} USDT</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="wallet-type-pill p-2 rounded-3 border border-dark bg--dark-three cursor-pointer d-flex align-items-center gap-2" data-val="funding" style="cursor: pointer; transition: all 0.2s ease;">
                                    <div class="wallet-icon-box text-muted fs-4">
                                        <i class="las la-wallet"></i>
                                    </div>
                                    <div>
                                        <strong class="text-white d-block text--small font-mono">@lang('Funding Wallet')</strong>
                                        <small class="text-muted font-mono" style="font-size: 11px;">${{ number_format($fundingBalance, 2) }} USDT</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="wallet_type" id="walletTypeSelect" value="spot" required>
                    </div>

                    <!-- Capital Allocation Amount -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-muted text--small text-uppercase mb-0">@lang('Capital ($)')</label>
                            <span class="text-muted text--small">@lang('Limit'): <span id="modalLimitsText" class="text--base font-mono"></span></span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg--dark-three text-white border-dark">$</span>
                            <input type="number" step="any" name="amount" class="form-control bg--dark-three text-white border-dark fs-5 font-mono" id="deployAmountInput" placeholder="0.00" required>
                            <span class="input-group-text bg--dark-three text-white border-dark">USDT</span>
                        </div>

                        <!-- Quick Percentage Pills -->
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-pct-btn font-mono" data-pct="25">25%</button>
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-pct-btn font-mono" data-pct="50">50%</button>
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-pct-btn font-mono" data-pct="75">75%</button>
                            <button type="button" class="btn btn-sm btn-outline--base flex-fill rounded-pill quick-pct-btn font-mono" data-pct="100">MAX</button>
                        </div>
                    </div>

                    <!-- Dynamic Institutional Risk Controls (Sliders) -->
                    <div class="bg--dark-three p-3 rounded-3 mb-3 border border-dark">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white text--small fw-bold d-flex align-items-center gap-1">
                                <i class="las la-shield-alt text--info"></i> @lang('Trailing Stop-Loss')
                            </span>
                            <span class="text--info font-mono fw-bold" id="trailingStopValue">2.0%</span>
                        </div>
                        <input type="range" class="form-range custom-range" name="trailing_stop_loss" id="trailingStopRange" min="0.5" max="10.0" step="0.5" value="2.0">
                        <small class="text-muted d-block">@lang('Dynamic risk ratchet automatically locks in profits and bounds maximum drawdown.')</small>
                    </div>

                    <div class="bg--dark-three p-3 rounded-3 mb-3 border border-dark">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white text--small fw-bold d-flex align-items-center gap-1">
                                <i class="las la-crosshairs text--success"></i> @lang('Take-Profit Target')
                            </span>
                            <span class="text--success font-mono fw-bold" id="takeProfitValue">5.0%</span>
                        </div>
                        <input type="range" class="form-range custom-range" name="take_profit_target" id="takeProfitRange" min="1.5" max="25.0" step="0.5" value="5.0">
                        <small class="text-muted d-block">@lang('Auto-executes profit harvest on price target clearance.')</small>
                    </div>

                    <!-- Projected Return & Institutional Telemetry -->
                    <div class="bg--dark-three p-3 rounded-3 mb-2 border border-dark">
                        <div class="d-flex justify-content-between align-items-center mb-2 text--small">
                            <span class="text-muted">@lang('Est. Daily Yield'):</span>
                            <strong class="text--success font-mono" id="estDailyProfit">+$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 text--small border-top border-dark pt-2">
                            <span class="text-muted">@lang('Max Risk Exposure'):</span>
                            <span class="text--danger font-mono fw-bold" id="estRiskExposure">-$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text--small border-top border-dark pt-2">
                            <span class="text-muted">@lang('Risk / Reward Ratio'):</span>
                            <span class="text--info font-mono fw-bold" id="riskRewardRatio">1 : 2.50</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-dark p-4">
                    <button type="submit" class="btn btn--base w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm">
                        <i class="las la-bolt me-1"></i> @lang('Activate Bot')
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
    .ai-terminal-wrapper {
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
    .ai-mobile-nav {
        border: 1px solid #334155;
    }
    .ai-mobile-nav .btn.active {
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
    .ai-metric-card {
        background: #0f172a;
        border: 1px solid #1e293b;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .ai-metric-card:hover {
        transform: translateY(-2px);
        border-color: #3b82f6;
    }
    .ai-icon-badge {
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
    
    .active-bot-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid #334155;
    }
    .bot-plan-card {
        background: #0f172a;
        border: 1px solid #1e293b;
        transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .bot-plan-card:hover {
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
    .roi-highlight-box {
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    .empty-ai-icon {
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
    .ai-signal-terminal {
        background: #020617;
        border: 1px solid #1e293b;
        font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', Courier, monospace;
        max-height: 220px;
        overflow-y: auto;
    }
    .custom-ai-table th {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
        font-size: 11px;
        text-transform: uppercase;
        border: none;
    }
    .custom-ai-table td {
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
        var selectedPlanMin = 0;
        var selectedPlanMax = 0;
        var selectedRoiMin = 0;

        // Sliders & Risk auto-calculation
        function calculateRiskAndReward(capital) {
            var stopLossPct = parseFloat($('#trailingStopRange').val()) || 2.0;
            var takeProfitPct = parseFloat($('#takeProfitRange').val()) || 5.0;

            // Trailing stop loss dollar calculation
            if (capital > 0) {
                var stopLossDollar = (capital * (stopLossPct / 100));
                $('#trailingStopValue').html(stopLossPct.toFixed(1) + '% <span class="badge bg-danger-soft text--danger ms-1">-$' + stopLossDollar.toFixed(2) + '</span>');
                $('#estRiskExposure').html('-$' + stopLossDollar.toFixed(2) + ' <span class="text-muted text--small font-mono">(' + stopLossPct.toFixed(1) + '%)</span>');
            } else {
                $('#trailingStopValue').text(stopLossPct.toFixed(1) + '%');
                $('#estRiskExposure').text('-$0.00 (' + stopLossPct.toFixed(1) + '%)');
            }

            // Take profit dollar calculation
            if (capital > 0) {
                var takeProfitDollar = (capital * (takeProfitPct / 100));
                $('#takeProfitValue').html(takeProfitPct.toFixed(1) + '% <span class="badge bg-success-soft text--success ms-1">+$' + takeProfitDollar.toFixed(2) + '</span>');
            } else {
                $('#takeProfitValue').html(takeProfitPct.toFixed(1) + '%');
            }

            // Risk / Reward Ratio Calculation
            if (stopLossPct > 0) {
                var rrRatio = (takeProfitPct / stopLossPct).toFixed(2);
                $('#riskRewardRatio').text('1 : ' + rrRatio);
            }

            // Risk-Adjusted Est. Daily Return Calculation
            // Functionally modulated by BOTH Take-Profit Target and Trailing Stop-Loss
            var baseRoi = selectedRoiMin > 0 ? selectedRoiMin : 1.5;
            var targetMultiplier = (0.7 + (takeProfitPct / 10.0) + (stopLossPct / 20.0));
            var effectiveDailyRoi = Math.max(0.5, (baseRoi * targetMultiplier));

            if (capital > 0) {
                var dailyProfit = (capital * (effectiveDailyRoi / 100));
                $('#estDailyProfit').html('+$' + dailyProfit.toFixed(2) + ' / day <span class="badge bg-success-soft text--success ms-1">(' + effectiveDailyRoi.toFixed(2) + '%)</span>');
            } else {
                $('#estDailyProfit').html('+$0.00 <span class="badge bg-success-soft text--success ms-1">(' + effectiveDailyRoi.toFixed(2) + '%)</span>');
            }
        }

        $(document).on('input change touchmove pointermove', '#trailingStopRange, #takeProfitRange', function() {
            var capital = parseFloat($('#deployAmountInput').val()) || 0;
            calculateRiskAndReward(capital);
        });

        $(document).on('input change keyup blur paste', '#deployAmountInput', function () {
            var amount = parseFloat($(this).val()) || 0;
            calculateRiskAndReward(amount);
        });

        $(document).on('change', '#walletTypeSelect', function () {
            var amount = parseFloat($('#deployAmountInput').val()) || 0;
            calculateRiskAndReward(amount);
        });

        // Toggle extended leaderboard bots
        $(document).on('click', '#toggleExtendedBotsBtn', function() {
            var isExpanded = $('.extended-bot-row').first().hasClass('d-none');
            if (isExpanded) {
                $('.extended-bot-row').removeClass('d-none');
                $('#toggleExtendedBotsText').text("@lang('Show Top 5 Strategies Only')");
                $(this).find('i').removeClass('la-angle-down').addClass('la-angle-up');
            } else {
                $('.extended-bot-row').addClass('d-none');
                $('#toggleExtendedBotsText').text("@lang('View All') {{ $copyTradingBots->count() }} @lang('Institutional Strategies')");
                $(this).find('i').removeClass('la-angle-up').addClass('la-angle-down');
            }
        });

        // Dynamic micro-fluctuations on leaderboard returns
        setInterval(function() {
            $('.live-return-val').each(function() {
                var base = parseFloat($(this).data('base')) || 100;
                var delta = (Math.random() * 0.4 - 0.2);
                var newVal = (base + delta).toFixed(1);
                $(this).text('+' + newVal + '%');
            });
        }, 4500);

        // Open Marketplace (Shows all options, never auto-selects)
        $(document).on('click', '.openMarketplaceBtn', function () {
            if ($(window).width() < 768) {
                $('#marketplaceModal').modal('show');
            } else {
                if ($("#desktopMarketplace").length) {
                    $('html, body').animate({
                        scrollTop: $("#desktopMarketplace").offset().top - 80
                    }, 400);
                } else {
                    $('#marketplaceModal').modal('show');
                }
            }
        });

        // Launch configure modal from plan
        $(document).on('click', '.deployBotBtn, .selectAndDeployBtn', function () {
            $('#marketplaceModal').modal('hide');
            var modal = $('#deployBotModal');
            var planId = $(this).data('id');
            var name = $(this).data('name');
            selectedPlanMin = parseFloat($(this).data('min')) || 50;
            selectedPlanMax = parseFloat($(this).data('max')) || 100000;
            selectedRoiMin = parseFloat($(this).data('roi_min')) || 1.5;
            var roiMax = $(this).data('roi_max') || 3.0;
            var duration = $(this).data('duration') || 30;

            modal.find('#modalPlanId').val(planId);
            modal.find('#modalBotName').text(name);
            modal.find('#modalRoiText').text(selectedRoiMin + '% - ' + roiMax + '%');
            modal.find('#modalDurationText').text(duration);
            modal.find('#modalLimitsText').text('$' + selectedPlanMin.toLocaleString() + ' - $' + selectedPlanMax.toLocaleString());
            modal.find('#deployAmountInput').val(selectedPlanMin);
            
            calculateRiskAndReward(selectedPlanMin);
            modal.modal('show');
        });

        // Percentage Pills with accurate proportional scaling
        $(document).on('click', '.quick-pct-btn', function () {
            var pct = parseFloat($(this).data('pct'));
            var walletType = $('#walletTypeSelect').val();
            var balance = (walletType === 'spot') ? currentSpotBalance : currentFundingBalance;
            var calculatedAmount = 0;

            var min = selectedPlanMin > 0 ? selectedPlanMin : 100;
            var max = selectedPlanMax > 0 ? selectedPlanMax : 5000;

            if (balance >= min) {
                var usableMax = Math.min(balance, max);
                if (pct === 100) {
                    calculatedAmount = usableMax;
                } else if (pct === 25) {
                    calculatedAmount = min + ((usableMax - min) * 0.25);
                } else if (pct === 50) {
                    calculatedAmount = min + ((usableMax - min) * 0.50);
                } else if (pct === 75) {
                    calculatedAmount = min + ((usableMax - min) * 0.75);
                }
            } else {
                // If balance is 0 or demo, scale cleanly across plan limits
                if (pct === 100) {
                    calculatedAmount = max;
                } else if (pct === 25) {
                    calculatedAmount = min;
                } else if (pct === 50) {
                    calculatedAmount = min + ((max - min) * 0.50);
                } else if (pct === 75) {
                    calculatedAmount = min + ((max - min) * 0.75);
                }
            }

            $('#deployAmountInput').val(calculatedAmount.toFixed(2));
            calculateRiskAndReward(calculatedAmount);
        });

        // Dynamic Running Bot Uptime Clock
        function updateUptime() {
            $('.bot-uptime').each(function() {
                var startIso = $(this).data('start');
                if (startIso) {
                    var start = new Date(startIso);
                    var diffMs = (new Date()) - start;
                    if (diffMs > 0) {
                        var diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
                        var diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                        var diffSecs = Math.floor((diffMs % (1000 * 60)) / 1000);
                        $(this).text(diffHrs + 'h ' + diffMins + 'm ' + diffSecs + 's');
                    }
                }
            });
        }
        setInterval(updateUptime, 1000);
        updateUptime();

        // Mobile Tabs Switcher - Exclusive visibility on mobile screens
        function handleMobileTabs() {
            if ($(window).width() < 768) {
                var activeTarget = $('.mobile-tab-btn.active').data('target') || '#activeBotsSection';
                $('.ai-content-section').addClass('d-none');
                $(activeTarget).removeClass('d-none');
            } else {
                $('.ai-content-section').removeClass('d-none');
            }
        }

        handleMobileTabs();
        $(window).on('resize', handleMobileTabs);

        $('.mobile-tab-btn').on('click', function() {
            $('.mobile-tab-btn').removeClass('active text-white').addClass('text-muted');
            $(this).addClass('active text-white').removeClass('text-muted');

            var target = $(this).data('target');
            $('.ai-content-section').addClass('d-none');
            $(target).removeClass('d-none');
        });

        // Real-Time Live Crypto Prices Cache
        var liveCryptoPrices = {
            'BTC/USDT': 77901.00,
            'ETH/USDT': 3120.00,
            'SOL/USDT': 195.40,
            'BNB/USDT': 640.00,
            'XRP/USDT': 0.5840,
            'AVAX/USDT': 28.50,
            'SUI/USDT': 1.95,
            'DOGE/USDT': 0.142,
            'NEAR/USDT': 4.85
        };

        // Fetch actual live ticker prices from Binance public endpoint
        fetch('https://api.binance.com/api/v3/ticker/price')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(item) {
                        if (item.symbol === 'BTCUSDT') liveCryptoPrices['BTC/USDT'] = parseFloat(item.price);
                        if (item.symbol === 'ETHUSDT') liveCryptoPrices['ETH/USDT'] = parseFloat(item.price);
                        if (item.symbol === 'SOLUSDT') liveCryptoPrices['SOL/USDT'] = parseFloat(item.price);
                        if (item.symbol === 'BNBUSDT') liveCryptoPrices['BNB/USDT'] = parseFloat(item.price);
                        if (item.symbol === 'XRPUSDT') liveCryptoPrices['XRP/USDT'] = parseFloat(item.price);
                        if (item.symbol === 'AVAXUSDT') liveCryptoPrices['AVAX/USDT'] = parseFloat(item.price);
                        if (item.symbol === 'SUIUSDT') liveCryptoPrices['SUI/USDT'] = parseFloat(item.price);
                        if (item.symbol === 'DOGEUSDT') liveCryptoPrices['DOGE/USDT'] = parseFloat(item.price);
                        if (item.symbol === 'NEARUSDT') liveCryptoPrices['NEAR/USDT'] = parseFloat(item.price);
                    });
                }
            })
            .catch(function(e) {
                console.log('Using fallback live price matrix');
            });

        // Real-Time Signal Stream Generator with Institutional Quant Formatting
        var signalTemplates = [
            { pair: 'BTC/USDT', type: 'SIGNAL-BUY', tag: 'text--success', text: 'Momentum Breakout &rarr; 98.6%' },
            { pair: 'ETH/USDT', type: 'ARBITRAGE', tag: 'text--info', text: 'Spread +0.42% captured' },
            { pair: 'SOL/USDT', type: 'TAKE-PROFIT', tag: 'text--success', text: 'Trailing stop locked' },
            { pair: 'BNB/USDT', type: 'QUANT-SCAN', tag: 'text--base', text: 'Compression band expansion' },
            { pair: 'XRP/USDT', type: 'DEPTH-SWEEP', tag: 'text--warning', text: 'Bid wall absorbed' },
            { pair: 'AVAX/USDT', type: 'SIGNAL-BUY', tag: 'text--success', text: 'Volume surge +280% on 15m' },
            { pair: 'SUI/USDT', type: 'TRAILING-STOP', tag: 'text--info', text: 'Trailing profit adjusted (+3.40%)' },
            { pair: 'NEAR/USDT', type: 'SIGNAL-BUY', tag: 'text--success', text: 'Mean reversion bounce fill' }
        ];

        setInterval(function() {
            var item = signalTemplates[Math.floor(Math.random() * signalTemplates.length)];
            var price = liveCryptoPrices[item.pair] || 77901.00;
            var formattedPrice = (price > 10) ? '$' + price.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '$' + price.toFixed(4);

            var now = new Date();
            var timeStr = now.toTimeString().split(' ')[0];

            var lineHtml = '<div class="signal-line text--small mb-2 font-mono" style="display:none;">' +
                '<span class="text-muted">[' + timeStr + ']</span> ' +
                '<span class="' + item.tag + ' fw-bold">[' + item.type + ']</span> ' +
                '<strong>' + item.pair + '</strong> (@ ' + formattedPrice + '): ' + item.text +
                '</div>';

            var line = $(lineHtml);
            $('#signalTerminal').prepend(line);
            line.fadeIn(300);

            if ($('#signalTerminal .signal-line').length > 15) {
                $('#signalTerminal .signal-line').last().remove();
            }
        }, 3500);

        // Web Audio API Institutional Synthesis Engine (100% Lightweight, No Assets)
        var SoundFX = (function() {
            var audioCtx = null;
            var isMuted = localStorage.getItem('vinance_sound_muted') === '1';

            function getCtx() {
                if (!audioCtx && (window.AudioContext || window.webkitAudioContext)) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                return audioCtx;
            }

            function updateUI() {
                if (isMuted) {
                    $('#soundToggleIcon').removeClass('la-volume-up text--info').addClass('la-volume-mute text-muted');
                    $('#soundToggleText').text("@lang('Muted')");
                } else {
                    $('#soundToggleIcon').removeClass('la-volume-mute text-muted').addClass('la-volume-up text--info');
                    $('#soundToggleText').text("@lang('Audio')");
                }
            }

            return {
                init: function() {
                    updateUI();
                    $('#soundToggleBtn').on('click', function() {
                        isMuted = !isMuted;
                        localStorage.setItem('vinance_sound_muted', isMuted ? '1' : '0');
                        updateUI();
                        if (!isMuted) SoundFX.play('click');
                    });
                },
                play: function(type) {
                    if (isMuted) return;
                    try {
                        var ctx = getCtx();
                        if (!ctx) return;
                        if (ctx.state === 'suspended') ctx.resume();

                        var now = ctx.currentTime;
                        var osc = ctx.createOscillator();
                        var gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);

                        if (type === 'click') {
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(1200, now);
                            gain.gain.setValueAtTime(0.08, now);
                            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.04);
                            osc.start(now);
                            osc.stop(now + 0.04);
                        } else if (type === 'success' || type === 'deploy') {
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(523.25, now);
                            osc.frequency.setValueAtTime(659.25, now + 0.08);
                            gain.gain.setValueAtTime(0.12, now);
                            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                            osc.start(now);
                            osc.stop(now + 0.3);
                        } else if (type === 'harvest') {
                            osc.type = 'triangle';
                            osc.frequency.setValueAtTime(783.99, now);
                            osc.frequency.setValueAtTime(1046.50, now + 0.07);
                            osc.frequency.setValueAtTime(1318.51, now + 0.14);
                            gain.gain.setValueAtTime(0.15, now);
                            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                            osc.start(now);
                            osc.stop(now + 0.45);
                        }
                    } catch (e) {}
                }
            };
        })();

        SoundFX.init();

        // Auto-Compound Toggle AJAX Handler
        $(document).on('change', '.bot-autocompound-toggle', function () {
            var botId = $(this).data('id');
            var isChecked = $(this).is(':checked');
            var toggleUrl = "{{ route('user.ai.bot.autocompound', ':id') }}".replace(':id', botId);
            var $this = $(this);

            SoundFX.play('click');

            $.ajax({
                url: toggleUrl,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (res) {
                    if (res.success) {
                        notify('success', res.message);
                        SoundFX.play('success');
                    } else {
                        $this.prop('checked', !isChecked);
                        notify('error', res.message || "@lang('Failed to update Auto-Compound')");
                    }
                },
                error: function () {
                    $this.prop('checked', !isChecked);
                    notify('error', "@lang('Server connection error')");
                }
            });
        });

        // AI Wallet Source Selection
        $(document).on('click', '#aiWalletTypePillsContainer .wallet-type-pill', function() {
            $('#aiWalletTypePillsContainer .wallet-type-pill').removeClass('active border--base');
            $('#aiWalletTypePillsContainer .wallet-type-pill .wallet-icon-box').addClass('text-muted').removeClass('text--base');
            $(this).addClass('active border--base');
            $(this).find('.wallet-icon-box').removeClass('text-muted').addClass('text--base');
            $('#walletTypeSelect').val($(this).data('val'));
            SoundFX.play('click');
        });

        // Mobile Tabs Switcher - Exclusive visibility on mobile screens (< 768px)
        function handleMobileAITabs() {
            if ($(window).width() < 768) {
                var activeTarget = $('.mobile-tab-btn.active').data('target') || '#activeBotsSection';
                $('.ai-content-section').addClass('d-none');
                $(activeTarget).removeClass('d-none');
            } else {
                $('.ai-content-section').removeClass('d-none');
            }
        }

        handleMobileAITabs();
        $(window).on('resize', handleMobileAITabs);

        $(document).on('click', '.mobile-tab-btn', function() {
            $('.mobile-tab-btn').removeClass('active text-white').addClass('text-muted');
            $(this).addClass('active text-white').removeClass('text-muted');

            var target = $(this).data('target');
            if ($(window).width() < 768) {
                $('.ai-content-section').addClass('d-none');
                $(target).removeClass('d-none');
            }
            SoundFX.play('click');
        });

        // Toggle Extended Active Bots
        $(document).on('click', '#toggleExtendedActiveBotsBtn', function() {
            var $hidden = $('.extended-active-bot');
            if ($hidden.first().hasClass('d-none')) {
                $hidden.removeClass('d-none');
                $('#toggleExtendedActiveBotsText').text("@lang('Show Less')");
                $(this).find('i').removeClass('la-angle-down').addClass('la-angle-up');
            } else {
                $hidden.addClass('d-none');
                $('#toggleExtendedActiveBotsText').text("@lang('Show All') " + $('.active-bot-card').length + " @lang('Active Bots')");
                $(this).find('i').removeClass('la-angle-up').addClass('la-angle-down');
            }
            SoundFX.play('click');
        });

        // Toggle Extended Copy Trading Strategies
        $(document).on('click', '#toggleExtendedBotsBtn', function() {
            var $hidden = $('.extended-bot-row');
            if ($hidden.first().hasClass('d-none')) {
                $hidden.removeClass('d-none');
                $('#toggleExtendedBotsText').text("@lang('Show Less Strategies')");
                $(this).find('i').removeClass('la-angle-down').addClass('la-angle-up');
            } else {
                $hidden.addClass('d-none');
                $('#toggleExtendedBotsText').text("@lang('View All') " + $('.custom-trades-table tbody tr').length + " @lang('Institutional Strategies')");
                $(this).find('i').removeClass('la-angle-up').addClass('la-angle-down');
            }
            SoundFX.play('click');
        });

        // Paginate Trade History 5 at a time
        var currentVisibleTrades = 5;
        $(document).on('click', '#loadMoreTradesBtn', function() {
            currentVisibleTrades += 5;
            $('.trade-log-row').each(function() {
                var idx = parseInt($(this).data('index'));
                if (idx <= currentVisibleTrades) {
                    $(this).removeClass('d-none');
                }
            });
            var totalTrades = $('.trade-log-row').length;
            if (currentVisibleTrades >= totalTrades) {
                $('#loadMoreTradesBtn').parent().hide();
            } else {
                var remaining = totalTrades - currentVisibleTrades;
                $('#loadMoreTradesText').text("@lang('Show 5 More Trades') (" + remaining + " @lang('remaining'))");
            }
            SoundFX.play('click');
        });
    })(jQuery);
</script>
@endpush