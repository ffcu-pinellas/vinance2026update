@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="ai-terminal-wrapper pb-5">
    <!-- Top Action & Navigation Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('user.home') }}" class="btn btn-sm btn-outline--light px-3 py-2 rounded-pill back-to-hub-btn">
                <i class="las la-arrow-left me-1"></i> @lang('Dashboard')
            </a>
            <div>
                <h3 class="mb-0 text-white fw-bold d-flex align-items-center gap-2">
                    <i class="las la-robot text--base"></i> @lang('Vinance AI Auto-Trader')
                    <span class="badge badge--success-outline rounded-pill text--small fw-normal px-2 py-1">
                        <span class="live-pulse-dot"></span> @lang('Neural Engine V4.2 Online')
                    </span>
                </h3>
                <p class="text-muted text--small mb-0">@lang('Autonomous high-frequency algorithmic quantitative trading terminal')</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('user.deposit.index') }}" class="btn btn-sm btn-outline--base rounded-pill px-3 py-2">
                <i class="las la-wallet me-1"></i> @lang('Deposit Capital')
            </a>
            <a href="#marketplaceSection" class="btn btn-sm btn--base rounded-pill px-4 py-2 shadow-sm">
                <i class="las la-plus-circle me-1"></i> @lang('Deploy New AI Bot')
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Total AI Capital -->
        <div class="col-xl-3 col-sm-6">
            <div class="ai-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Active Bot Capital')</span>
                    <div class="ai-icon-badge bg-primary-soft text--base">
                        <i class="las la-coins"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1">${{ showAmount($totalAllocated) }}</h3>
                <div class="d-flex align-items-center justify-content-between text--small">
                    <span class="text-muted">@lang('Spot Balance'): <strong class="text-white">${{ showAmount($spotBalance) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Total AI Profit -->
        <div class="col-xl-3 col-sm-6">
            <div class="ai-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Total Realized Profit')</span>
                    <div class="ai-icon-badge bg-success-soft text--success">
                        <i class="las la-chart-line"></i>
                    </div>
                </div>
                <h3 class="text--success fw-bold mb-1">+${{ showAmount($totalProfit) }}</h3>
                <div class="d-flex align-items-center justify-content-between text--small">
                    <span class="badge badge--success-soft rounded-pill px-2"><i class="las la-arrow-up"></i> @lang('Cumulative Net PnL')</span>
                </div>
            </div>
        </div>

        <!-- Win Rate -->
        <div class="col-xl-3 col-sm-6">
            <div class="ai-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Algorithmic Win Rate')</span>
                    <div class="ai-icon-badge bg-info-soft text--info">
                        <i class="las la-shield-alt"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1">{{ number_format($winRate, 1) }}%</h3>
                <div class="d-flex align-items-center text--small text-muted">
                    <i class="las la-check-circle text--success me-1"></i> @lang('Multi-Indicator Consensus')
                </div>
            </div>
        </div>

        <!-- Active Bots -->
        <div class="col-xl-3 col-sm-6">
            <div class="ai-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Deployed AI Bots')</span>
                    <div class="ai-icon-badge bg-warning-soft text--warning">
                        <i class="las la-microchip"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1">{{ $activeBots->count() }} <span class="text-muted fs-6 fw-normal">/ {{ $plans->count() }} @lang('Max')</span></h3>
                <div class="d-flex align-items-center text--small text-muted">
                    <span class="live-pulse-dot me-1"></span> {{ $totalTrades }} @lang('Trades Executed')
                </div>
            </div>
        </div>
    </div>

    <!-- Active Bots Section -->
    <div class="card bg--dark-two border-0 rounded-4 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-4">
            <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                <i class="las la-server text--base"></i> @lang('My Active AI Bots')
                <span class="badge badge--primary rounded-pill">{{ $activeBots->count() }}</span>
            </h5>
            <a href="#marketplaceSection" class="btn btn-sm btn-outline--base rounded-pill px-3">
                <i class="las la-plus"></i> @lang('Add Bot')
            </a>
        </div>
        <div class="card-body p-4">
            @if($activeBots->count() > 0)
                <div class="row g-3">
                    @foreach($activeBots as $userBot)
                        <div class="col-lg-6 col-xxl-4">
                            <div class="active-bot-card p-4 rounded-4 position-relative overflow-hidden">
                                <div class="active-bot-glow"></div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="text-white fw-bold mb-1">{{ @$userBot->plan->name }}</h5>
                                        <span class="badge badge--dark text-uppercase text--small">{{ @$userBot->plan->strategy_type }} Strategy</span>
                                    </div>
                                    <span class="badge badge--success-soft rounded-pill px-3 py-1 d-flex align-items-center gap-1">
                                        <span class="live-pulse-dot"></span> @lang('RUNNING')
                                    </span>
                                </div>

                                <div class="row g-2 mb-3 bg--dark-three p-3 rounded-3">
                                    <div class="col-6">
                                        <small class="text-muted text-uppercase d-block">@lang('Allocated Capital')</small>
                                        <strong class="text-white fs-6">${{ showAmount($userBot->allocated_amount) }}</strong>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted text-uppercase d-block">@lang('Current Profit')</small>
                                        <strong class="text--success fs-6">+${{ showAmount($userBot->current_profit) }}</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted text-uppercase d-block">@lang('Target Daily ROI')</small>
                                        <span class="text--base fw-semibold">{{ @$userBot->plan->daily_roi_min }}% - {{ @$userBot->plan->daily_roi_max }}%</span>
                                    </div>
                                    <div class="col-6 text-end mt-2">
                                        <small class="text-muted text-uppercase d-block">@lang('Total Trades')</small>
                                        <span class="text-white">{{ $userBot->total_trades }} @lang('executed')</span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    @if($userBot->current_profit > 0)
                                        <form action="{{ route('user.ai.bot.harvest', $userBot->id) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn--success w-100 rounded-pill py-2">
                                                <i class="las la-hand-holding-usd me-1"></i> @lang('Harvest') (${{ showAmount($userBot->current_profit) }})
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('user.ai.bot.stop', $userBot->id) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline--danger w-100 rounded-pill py-2 confirmationBtn" data-question="@lang('Stop this bot and return allocated capital + profits to your Spot Wallet?')">
                                            <i class="las la-stop-circle me-1"></i> @lang('Pause & Refund')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-ai-icon mb-3">
                        <i class="las la-robot"></i>
                    </div>
                    <h5 class="text-white mb-2">@lang('No AI Trading Bots Currently Deployed')</h5>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 460px;">
                        @lang('Select one of our institutional neural strategies below to begin automated 24/7 high-frequency quantitative trading.')
                    </p>
                    <a href="#marketplaceSection" class="btn btn--base rounded-pill px-4 py-2">
                        <i class="las la-rocket me-1"></i> @lang('Explore & Deploy AI Bot')
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Bot Strategy Marketplace -->
    <div id="marketplaceSection" class="mb-5 pt-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="text-white fw-bold mb-1"><i class="las la-brain text--base"></i> @lang('AI Strategy Marketplace')</h4>
                <p class="text-muted text--small mb-0">@lang('Institutional neural algorithms calibrated for maximum risk-adjusted alpha')</p>
            </div>
        </div>

        <div class="row g-4">
            @foreach($plans as $plan)
                <div class="col-xl-3 col-md-6">
                    <div class="bot-plan-card h-100 p-4 rounded-4 d-flex flex-column justify-content-between position-relative">
                        @if($loop->first)
                            <div class="popular-ribbon">@lang('MOST POPULAR')</div>
                        @endif

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge badge--{{ $plan->risk_level == 'low' ? 'success' : ($plan->risk_level == 'medium' ? 'warning' : 'danger') }}-soft rounded-pill px-3 py-1 text-uppercase">
                                    {{ $plan->risk_level }} @lang('Risk')
                                </span>
                                <span class="text-muted text--small"><i class="las la-history"></i> {{ $plan->trade_duration_days }} @lang('Days')</span>
                            </div>

                            <h4 class="text-white fw-bold mb-1">{{ __($plan->name) }}</h4>
                            <p class="text-muted text--small mb-3">{{ __($plan->tagline) }}</p>

                            <!-- Daily ROI Box -->
                            <div class="roi-highlight-box p-3 rounded-3 mb-3 text-center">
                                <span class="text-muted text--small text-uppercase d-block mb-1">@lang('Target Daily Return')</span>
                                <h3 class="text--base fw-bold mb-0">{{ $plan->daily_roi_min }}% - {{ $plan->daily_roi_max }}%</h3>
                                <small class="text-muted">@lang('Win Rate'): <strong class="text--success">{{ $plan->win_rate }}%</strong></small>
                            </div>

                            <!-- Min / Max Investment -->
                            <div class="d-flex justify-content-between text--small mb-3 bg--dark-three p-2 rounded-2">
                                <span class="text-muted">@lang('Min Capital'): <strong class="text-white">${{ showAmount($plan->min_investment) }}</strong></span>
                                <span class="text-muted">@lang('Max'): <strong class="text-white">${{ showAmount($plan->max_investment) }}</strong></span>
                            </div>

                            <!-- Features List -->
                            <ul class="plan-features-list list-unstyled mb-4 text--small">
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

    <!-- Live AI Signal Feed & Market Intelligence -->
    <div class="row g-4 mb-4">
        <!-- Live AI Signal Stream -->
        <div class="col-lg-6">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-4">
                    <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                        <i class="las la-bolt text--base"></i> @lang('Live AI Signal Stream')
                        <span class="badge badge--success-soft rounded-pill text--small"><span class="live-pulse-dot"></span> @lang('LIVE')</span>
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="ai-signal-terminal p-3 rounded-3" id="signalTerminal">
                        <div class="signal-line text--small mb-2">
                            <span class="text-muted">[{{ date('H:i:s') }}]</span> <span class="text--base fw-bold">[QUANT-SCAN]</span> Scanning 48 spot pairs via low-latency WebSocket feed...
                        </div>
                        <div class="signal-line text--small mb-2">
                            <span class="text-muted">[{{ date('H:i:s', time()-15) }}]</span> <span class="text--success fw-bold">[SIGNAL-BUY]</span> BTC/USDT Momentum Breakout detected at $64,280.00 &rarr; Consensus 98.2%
                        </div>
                        <div class="signal-line text--small mb-2">
                            <span class="text-muted">[{{ date('H:i:s', time()-32) }}]</span> <span class="text--info fw-bold">[ARBITRAGE]</span> Cross-exchange spread 0.42% detected on ETH/USDT &rarr; Executing atomic fill
                        </div>
                        <div class="signal-line text--small mb-2">
                            <span class="text-muted">[{{ date('H:i:s', time()-54) }}]</span> <span class="text--success fw-bold">[TAKE-PROFIT]</span> SOL/USDT target hit (+2.84%) &rarr; Profit locked to active portfolio
                        </div>
                        <div class="signal-line text--small mb-2">
                            <span class="text-muted">[{{ date('H:i:s', time()-78) }}]</span> <span class="text--warning fw-bold">[RISK-GUARD]</span> Volatility spike checked &rarr; Dynamic stop protection engaged
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Strategy Architecture Insights -->
        <div class="col-lg-6">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-4">
                    <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                        <i class="las la-network-wired text--base"></i> @lang('Neural Consensus Metrics')
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg--dark-three p-3 rounded-3">
                                <small class="text-muted text-uppercase d-block mb-1">@lang('Execution Latency')</small>
                                <h4 class="text-white fw-bold mb-0">1.8 <span class="fs-6 text-muted">ms</span></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg--dark-three p-3 rounded-3">
                                <small class="text-muted text-uppercase d-block mb-1">@lang('Sharpe Ratio')</small>
                                <h4 class="text--success fw-bold mb-0">4.82</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg--dark-three p-3 rounded-3">
                                <small class="text-muted text-uppercase d-block mb-1">@lang('Max Drawdown')</small>
                                <h4 class="text-white fw-bold mb-0">1.4%</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg--dark-three p-3 rounded-3">
                                <small class="text-muted text-uppercase d-block mb-1">@lang('Security Protocol')</small>
                                <h4 class="text--base fw-bold mb-0"><i class="las la-lock"></i> AES-256</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trade Execution Logs Table -->
    <div class="card bg--dark-two border-0 rounded-4 shadow-sm">
        <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-4">
            <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                <i class="las la-history text--base"></i> @lang('AI Trade Execution History')
            </h5>
            <span class="text-muted text--small">{{ $tradeLogs->count() }} @lang('Total Records')</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0 custom-ai-table">
                    <thead>
                        <tr>
                            <th class="ps-4">@lang('Date / Time')</th>
                            <th>@lang('Pair')</th>
                            <th>@lang('Side')</th>
                            <th class="text-end">@lang('Entry Price')</th>
                            <th class="text-end">@lang('Exit Price')</th>
                            <th class="text-end">@lang('Volume')</th>
                            <th class="text-end">@lang('Profit')</th>
                            <th class="text-center pe-4">@lang('Status')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tradeLogs as $trade)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-white fw-medium">{{ showDateTime($trade->created_at, 'M d, Y') }}</span>
                                    <small class="text-muted d-block">{{ showDateTime($trade->created_at, 'H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge badge--dark px-2 py-1 fw-bold">{{ $trade->pair_symbol }}</span>
                                </td>
                                <td>
                                    <span class="badge badge--{{ $trade->side == 'BUY' ? 'success' : 'danger' }}-soft px-3 py-1">
                                        {{ $trade->side }}
                                    </span>
                                </td>
                                <td class="text-end fw-medium text-white">${{ showAmount($trade->entry_price) }}</td>
                                <td class="text-end fw-medium text-white">${{ showAmount($trade->exit_price) }}</td>
                                <td class="text-end fw-medium text-white">${{ showAmount($trade->amount) }}</td>
                                <td class="text-end">
                                    <span class="text--success fw-bold">+${{ showAmount($trade->profit_amount) }}</span>
                                    <small class="text-muted d-block">(+{{ $trade->profit_percentage }}%)</small>
                                </td>
                                <td class="text-center pe-4">
                                    <span class="badge badge--success-soft rounded-pill px-3 py-1">
                                        <i class="las la-check"></i> @lang('CLOSED')
                                    </span>
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
    </div>
</div>

<!-- Deploy Bot Modal -->
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
                        <span class="text-muted text--small text-uppercase d-block mb-1">@lang('Estimated Daily ROI')</span>
                        <h3 class="text--base fw-bold mb-0" id="modalRoiText">1.50% - 3.20%</h3>
                        <small class="text-muted">@lang('Contract Duration'): <strong class="text-white" id="modalDurationText">30</strong> @lang('Days')</small>
                    </div>

                    <!-- Wallet Selection -->
                    <div class="form-group mb-3">
                        <label class="form-label text-muted text--small text-uppercase">@lang('Funding Wallet Source')</label>
                        <select name="wallet_type" class="form-control form-select bg--dark-three text-white border-dark" id="walletTypeSelect" required>
                            <option value="spot" selected>@lang('Spot Wallet') (${{ showAmount($spotBalance) }} USDT)</option>
                            <option value="funding">@lang('Funding Wallet') (${{ showAmount($fundingBalance) }} USDT)</option>
                        </select>
                    </div>

                    <!-- Capital Allocation Amount -->
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-muted text--small text-uppercase mb-0">@lang('Allocation Capital ($)')</label>
                            <span class="text-muted text--small">@lang('Limit'): <span id="modalLimitsText" class="text--base"></span></span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg--dark-three text-white border-dark">$</span>
                            <input type="number" step="any" name="amount" class="form-control bg--dark-three text-white border-dark fs-5" id="deployAmountInput" placeholder="0.00" required>
                            <span class="input-group-text bg--dark-three text-white border-dark">USDT</span>
                        </div>

                        <!-- Quick Percentage Pills -->
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-pct-btn" data-pct="25">25%</button>
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-pct-btn" data-pct="50">50%</button>
                            <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill quick-pct-btn" data-pct="75">75%</button>
                            <button type="button" class="btn btn-sm btn-outline--base flex-fill rounded-pill quick-pct-btn" data-pct="100">MAX</button>
                        </div>
                    </div>

                    <!-- Projected Return -->
                    <div class="d-flex justify-content-between text--small bg--dark-three p-3 rounded-3 mb-2">
                        <span class="text-muted">@lang('Est. Daily Profit'):</span>
                        <strong class="text--success" id="estDailyProfit">+$0.00</strong>
                    </div>
                </div>
                <div class="modal-footer border-top border-dark p-4">
                    <button type="submit" class="btn btn--base w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm">
                        <i class="las la-bolt me-1"></i> @lang('Confirm & Activate Bot')
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
    }
    .bg--dark-two {
        background: #0f172a !important;
    }
    .bg--dark-three {
        background: #1e293b !important;
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
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
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
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
    }
    .ai-signal-terminal {
        background: #020617;
        border: 1px solid #1e293b;
        font-family: 'Courier New', Courier, monospace;
        max-height: 220px;
        overflow-y: auto;
    }
    .custom-ai-table th {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
        font-size: 12px;
        text-transform: uppercase;
        border: none;
    }
    .custom-ai-table td {
        border-bottom: 1px solid #1e293b !important;
        padding: 12px 8px;
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

        $('.deployBotBtn').on('click', function () {
            var modal = $('#deployBotModal');
            var planId = $(this).data('id');
            var name = $(this).data('name');
            selectedPlanMin = parseFloat($(this).data('min'));
            selectedPlanMax = parseFloat($(this).data('max'));
            selectedRoiMin = parseFloat($(this).data('roi_min'));
            var roiMax = $(this).data('roi_max');
            var duration = $(this).data('duration');

            modal.find('#modalPlanId').val(planId);
            modal.find('#modalBotName').text(name);
            modal.find('#modalRoiText').text(selectedRoiMin + '% - ' + roiMax + '%');
            modal.find('#modalDurationText').text(duration);
            modal.find('#modalLimitsText').text('$' + selectedPlanMin.toLocaleString() + ' - $' + selectedPlanMax.toLocaleString());
            modal.find('#deployAmountInput').val(selectedPlanMin);
            
            calculateEstimatedProfit(selectedPlanMin);
            modal.modal('show');
        });

        $('#deployAmountInput').on('input', function () {
            var amount = parseFloat($(this).val()) || 0;
            calculateEstimatedProfit(amount);
        });

        $('.quick-pct-btn').on('click', function () {
            var pct = parseFloat($(this).data('pct'));
            var walletType = $('#walletTypeSelect').val();
            var balance = (walletType === 'spot') ? currentSpotBalance : currentFundingBalance;
            var calculatedAmount = (balance * (pct / 100));

            if (selectedPlanMax && calculatedAmount > selectedPlanMax) {
                calculatedAmount = selectedPlanMax;
            }

            $('#deployAmountInput').val(calculatedAmount.toFixed(2));
            calculateEstimatedProfit(calculatedAmount);
        });

        function calculateEstimatedProfit(amount) {
            if (amount > 0 && selectedRoiMin > 0) {
                var dailyProfit = (amount * (selectedRoiMin / 100));
                $('#estDailyProfit').text('+$' + dailyProfit.toFixed(2) + ' / day');
            } else {
                $('#estDailyProfit').text('+$0.00');
            }
        }

        // Live Simulated Signal Feed Updater
        setInterval(function() {
            var pairs = ['BTC/USDT', 'ETH/USDT', 'SOL/USDT', 'BNB/USDT', 'AVAX/USDT', 'XRP/USDT'];
            var randomPair = pairs[Math.floor(Math.random() * pairs.length)];
            var signals = [
                '<span class="text--success fw-bold">[SIGNAL-BUY]</span> ' + randomPair + ' Momentum Surge &rarr; 97.4% Consensus',
                '<span class="text--info fw-bold">[DEPTH-SCAN]</span> ' + randomPair + ' Orderbook spread tightened to 0.01%',
                '<span class="text--base fw-bold">[NEURAL-SYNC]</span> Recalibrated risk bounds for ' + randomPair + ' (Volatility: Low)'
            ];
            var randomSignal = signals[Math.floor(Math.random() * signals.length)];
            var now = new Date();
            var timeStr = now.toTimeString().split(' ')[0];

            var line = $('<div class="signal-line text--small mb-2" style="display:none;"><span class="text-muted">[' + timeStr + ']</span> ' + randomSignal + '</div>');
            $('#signalTerminal').prepend(line);
            line.fadeIn(300);

            if ($('#signalTerminal .signal-line').length > 15) {
                $('#signalTerminal .signal-line').last().remove();
            }
        }, 5000);
    })(jQuery);
</script>
@endpush