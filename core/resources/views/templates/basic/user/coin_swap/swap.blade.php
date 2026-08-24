@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="coin-swap-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <span class="badge badge--success-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
            <span class="live-pulse-dot"></span> @lang('Institutional Liquidity Online')
        </span>
    </div>

    <!-- Main Header Card -->
    <div class="swap-header-card bg--dark-two p-3 p-md-4 rounded-4 mb-4 border-0 shadow-sm">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h3 class="text-white fw-bold mb-1 fs-4 d-flex align-items-center gap-2">
                    <i class="las la-sync text--base"></i> @lang('Instant Coin Swap')
                    @if($feeRate == 0)
                        <span class="badge badge--success-soft rounded-pill text--small fw-normal px-2 py-1">
                            <i class="las la-tag"></i> @lang('0% VIP Fee Active')
                        </span>
                    @endif
                </h3>
                <p class="text-muted text--small mb-0">@lang('0-Slippage Guaranteed Execution with Institutional Market Depth')</p>
            </div>

            <!-- Telemetry Pill -->
            <div class="user-wallet-pill bg--dark-three border border-dark rounded-pill px-3 py-2 d-flex align-items-center gap-2 text--small">
                <span class="text-muted"><i class="las la-bolt text--warning"></i> @lang('Execution'): <strong class="text--success">@lang('Instant (0s)')</strong></span>
                <span class="text-muted opacity-50">|</span>
                <span class="text-muted"><i class="las la-percentage text--info"></i> @lang('Fee'): <strong class="text-white font-mono">{{ $feeRate }}%</strong></span>
            </div>
        </div>
    </div>

    <!-- Mobile View Tab Switcher (Visible only on Mobile screens) -->
    <div class="d-md-none mb-3">
        <div class="swap-mobile-nav p-1 rounded-pill bg--dark-two d-flex shadow-sm">
            <button type="button" class="btn btn-sm text-white flex-fill rounded-pill py-2 active mobile-swap-tab-btn" data-target="#swapTerminalSection">
                <i class="las la-exchange-alt me-1"></i> @lang('Convert')
            </button>
            <button type="button" class="btn btn-sm text-muted flex-fill rounded-pill py-2 mobile-swap-tab-btn" data-target="#swapHistorySection">
                <i class="las la-history me-1"></i> @lang('History') ({{ $swaps->total() }})
            </button>
        </div>
    </div>

    <!-- FULL WIDTH SWAP TERMINAL & TELEMETRY GRID -->
    <div class="row g-4">
        <!-- Main Converter Card (Full Width / Balanced) -->
        <div id="swapTerminalSection" class="swap-content-section col-12">
            <div class="swap-terminal-card bg--dark-two p-4 p-md-5 rounded-4 shadow-lg border-0 mb-4">
                <div class="row align-items-center g-4">
                    <!-- Left Column: Converter Form -->
                    <div class="col-lg-7">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-white mb-0 fw-bold d-flex align-items-center gap-2">
                                <i class="las la-coins text--base"></i> @lang('Convert & Swap Assets')
                            </h5>
                            <span class="badge badge--dark px-3 py-1 font-mono text-muted">
                                <i class="las la-shield-alt text--success me-1"></i> @lang('Spot Wallet')
                            </span>
                        </div>

                        <form id="instantSwapForm" method="POST">
                            @csrf

                            <!-- "FROM" (PAY) INPUT BOX -->
                            <div class="swap-input-box p-3 p-sm-4 rounded-3 mb-2 bg--dark-three border border-dark">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('From (Pay)')</span>
                                    <span class="text-muted text--small">
                                        @lang('Balance'): <strong class="text-white font-mono" id="fromUserBalance">0.00</strong>
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <input type="number" step="any" name="amount" class="form-control bg-transparent text-white border-0 fs-3 fw-bold font-mono p-0 shadow-none" id="swapAmountInput" placeholder="0.0" required>
                                    
                                    <select name="from_currency" class="form-select form-control custom-coin-select flex-shrink-0" id="fromCurrencySelect" required style="min-width: 130px; max-width: 160px;">
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" data-symbol="{{ $currency->symbol }}" data-balance="{{ $currency->user_balance }}" {{ $loop->first ? 'selected' : '' }}>
                                                {{ $currency->symbol }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quick Percentage Pills -->
                                <div class="d-flex gap-2 mt-3 pt-2 border-top border-dark">
                                    <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill py-1 text--small quick-pct-btn font-mono" data-pct="25">25%</button>
                                    <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill py-1 text--small quick-pct-btn font-mono" data-pct="50">50%</button>
                                    <button type="button" class="btn btn-sm btn-outline--light flex-fill rounded-pill py-1 text--small quick-pct-btn font-mono" data-pct="75">75%</button>
                                    <button type="button" class="btn btn-sm btn-outline--base flex-fill rounded-pill py-1 text--small quick-pct-btn font-mono" data-pct="100">MAX</button>
                                </div>
                            </div>

                            <!-- ANIMATED INTERACTIVE SWAP DIRECTION BUTTON -->
                            <div class="d-flex justify-content-center my-1 position-relative" style="z-index: 5;">
                                <button type="button" class="btn btn--base rounded-circle swap-direction-btn shadow" id="flipDirectionBtn" title="Flip conversion direction">
                                    <i class="las la-exchange-alt rotate-icon"></i>
                                </button>
                            </div>

                            <!-- "TO" (RECEIVE) INPUT BOX -->
                            <div class="swap-input-box p-3 p-sm-4 rounded-3 mb-3 bg--dark-three border border-dark">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('To (Receive Estimated)')</span>
                                    <span class="text-muted text--small">
                                        @lang('Balance'): <strong class="text-white font-mono" id="toUserBalance">0.00</strong>
                                    </span>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <input type="text" class="form-control bg-transparent text--success border-0 fs-3 fw-bold font-mono p-0 shadow-none" id="swapReceivePreview" placeholder="0.0" readonly>

                                    <select name="to_currency" class="form-select form-control custom-coin-select flex-shrink-0" id="toCurrencySelect" required style="min-width: 130px; max-width: 160px;">
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" data-symbol="{{ $currency->symbol }}" data-balance="{{ $currency->user_balance }}" {{ $loop->iteration == 2 ? 'selected' : '' }}>
                                                {{ $currency->symbol }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- SWAP ACTION BUTTON -->
                            <button type="submit" class="btn btn--base w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2 mt-3" id="submitSwapBtn">
                                <i class="las la-sync-alt fs-5"></i> <span>@lang('Convert & Swap Now')</span>
                            </button>
                        </form>
                    </div>

                    <!-- Right Column: Live Quotation & Execution Analytics -->
                    <div class="col-lg-5">
                        <div class="bg--dark-three p-4 rounded-4 border border-dark h-100 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="las la-chart-bar text--base"></i> @lang('Live Execution Telemetry')
                                </h6>

                                <div class="p-3 rounded-3 bg--dark-two mb-3 text-center border border-dark">
                                    <span class="text-muted text--small text-uppercase d-block mb-1">@lang('Guaranteed Exchange Rate')</span>
                                    <h4 class="text-white fw-bold mb-0 font-mono" id="liveRateDisplay">
                                        <span class="spinner-border spinner-border-sm text--base me-1"></span> @lang('Fetching Rate...')
                                    </h4>
                                    <small class="text--success"><i class="las la-check-circle"></i> @lang('Vinance Institutional Liquidity Matrix')</small>
                                </div>

                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between py-2 border-bottom border-dark text--small">
                                        <span class="text-muted">@lang('Transaction Fee'):</span>
                                        <span class="text-white font-mono" id="liveFeeDisplay">$0.00</span>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 border-bottom border-dark text--small">
                                        <span class="text-muted">@lang('Slippage Tolerance'):</span>
                                        <span class="text--success font-mono">0.00% (@lang('Guaranteed'))</span>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 border-bottom border-dark text--small">
                                        <span class="text-muted">@lang('Settlement Speed'):</span>
                                        <span class="text-white font-mono">@lang('Instant Sub-second')</span>
                                    </li>
                                    <li class="d-flex justify-content-between py-2 text--small">
                                        <span class="text-muted">@lang('Net Settlement Output'):</span>
                                        <span class="text--base fw-bold font-mono" id="finalReceiveDisplay">0.00</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-4 p-3 rounded-3 bg--dark-two border border-dark text-muted text--small">
                                <i class="las la-info-circle text--info me-1"></i>
                                @lang('Converted assets are settled directly into your Spot Balance with zero-directional market price slippage.')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Swap History Table -->
        <div id="swapHistorySection" class="swap-content-section col-12">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4">
                    <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                        <i class="las la-history text--base"></i> @lang('My Conversion History')
                    </h5>
                    <span class="text-muted text--small font-mono">{{ $swaps->total() }} @lang('Total Swaps')</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0 custom-swap-table">
                            <thead>
                                <tr>
                                    <th class="ps-3 ps-sm-4">@lang('Date & Time')</th>
                                    <th>@lang('Sold (From)')</th>
                                    <th>@lang('Received (To)')</th>
                                    <th class="text-end">@lang('Exchange Rate')</th>
                                    <th class="text-end">@lang('Fee')</th>
                                    <th class="text-center pe-3 pe-sm-4">@lang('Status')</th>
                                </tr>
                            </thead>
                            <tbody id="swapHistoryTableBody">
                                @forelse($swaps as $swap)
                                    <tr>
                                        <td class="ps-3 ps-sm-4 text-nowrap font-mono">
                                            <span class="text-white fw-medium">{{ $swap->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted d-block">{{ $swap->created_at->format('H:i:s') }}</small>
                                        </td>
                                        <td>
                                            <span class="text--danger fw-bold font-mono">-{{ number_format($swap->from_amount, 6) }}</span>
                                            <span class="badge badge--dark ms-1">{{ @$swap->fromCurrency->symbol }}</span>
                                        </td>
                                        <td>
                                            <span class="text--success fw-bold font-mono">+{{ number_format($swap->to_amount, 6) }}</span>
                                            <span class="badge badge--dark ms-1">{{ @$swap->toCurrency->symbol }}</span>
                                        </td>
                                        <td class="text-end font-mono text-white">
                                            1 {{ @$swap->fromCurrency->symbol }} ≈ {{ number_format($swap->rate, 6) }} {{ @$swap->toCurrency->symbol }}
                                        </td>
                                        <td class="text-end font-mono text-muted">
                                            {{ number_format($swap->charge, 6) }}
                                        </td>
                                        <td class="text-center pe-3 pe-sm-4">
                                            @if($swap->status == 1)
                                                <span class="badge badge--success-soft rounded-pill px-3 py-1 font-mono">
                                                    <i class="las la-check-circle me-1"></i> @lang('SUCCESS')
                                                </span>
                                            @else
                                                <span class="badge badge--danger-soft rounded-pill px-3 py-1 font-mono">
                                                    @lang('REVERTED')
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptySwapRow">
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="las la-sync fs-2 mb-2 d-block"></i>
                                            @lang('No coin swap records found yet. Use the converter above for instant swaps.')
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($swaps->hasPages())
                    <div class="card-footer bg-transparent border-top border-dark py-3 px-4">
                        {{ paginateLinks($swaps) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .coin-swap-wrapper {
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
    .swap-mobile-nav {
        border: 1px solid #334155;
    }
    .swap-mobile-nav .btn.active {
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
    .swap-terminal-card {
        border: 1px solid #1e293b;
    }
    .swap-input-box {
        transition: border-color 0.2s ease;
    }
    .swap-input-box:focus-within {
        border-color: #3b82f6 !important;
    }
    .swap-direction-btn {
        width: 42px;
        height: 42px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        border: 3px solid #0f172a;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .swap-direction-btn:hover {
        transform: rotate(180deg) scale(1.1);
    }
    .custom-coin-select,
    select.custom-coin-select {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border: 1px solid #475569 !important;
        border-radius: 20px !important;
        padding: 8px 30px 8px 14px !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        font-family: 'JetBrains Mono', monospace !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%233b82f6' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.7rem center !important;
        background-size: 14px 10px !important;
    }
    .custom-coin-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
    }
    .badge--success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge--danger-soft { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .custom-swap-table th {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
        font-size: 11px;
        text-transform: uppercase;
        border: none;
    }
    .custom-swap-table td {
        border-bottom: 1px solid #1e293b !important;
        padding: 12px 8px;
        font-size: 13px;
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

        var calculateUrl = "{{ route('user.coin.swap.calculate') }}";
        var swapUrl = "{{ route('user.coin.swap') }}";
        var currentRate = 0;

        var clientPrices = {
            'BTC': 77901.50,
            'ETH': 2450.20,
            'SOL': 145.80,
            'BNB': 595.40,
            'XRP': 0.584,
            'DOGE': 0.115,
            'ADA': 0.385,
            'AVAX': 24.50,
            'LINK': 11.20,
            'DOT': 4.60,
            'LTC': 68.40,
            'NEAR': 4.80,
            'SUI': 1.95,
            'TRX': 0.165,
            'MATIC': 0.42,
            'TON': 5.20,
            'SHIB': 0.000018,
            'PEPE': 0.0000095,
            'UNI': 7.80,
            'ATOM': 4.50,
            'BCH': 345.00,
            'USDT': 1.0,
            'USD': 1.0,
            'USDC': 1.0,
            'BUSD': 1.0
        };

        // Try to fetch latest Binance live prices in browser
        fetch('https://api.binance.com/api/v3/ticker/price')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (Array.isArray(data)) {
                    data.forEach(function(item) {
                        if (item.symbol && item.symbol.endsWith('USDT')) {
                            var sym = item.symbol.replace('USDT', '');
                            clientPrices[sym] = parseFloat(item.price);
                        }
                    });
                    calculateQuotation();
                }
            })
            .catch(function() {});

        function updateSelectedBalances() {
            var fromOpt = $('#fromCurrencySelect').find(':selected');
            var toOpt = $('#toCurrencySelect').find(':selected');

            var fromSymbol = fromOpt.data('symbol') || fromOpt.attr('data-symbol') || fromOpt.text().trim() || 'BTC';
            var toSymbol = toOpt.data('symbol') || toOpt.attr('data-symbol') || toOpt.text().trim() || 'USDT';

            var fromBal = parseFloat(fromOpt.attr('data-balance')) || 0;
            var toBal = parseFloat(toOpt.attr('data-balance')) || 0;

            $('#fromUserBalance').text(fromBal.toFixed(6) + ' ' + fromSymbol);
            $('#toUserBalance').text(toBal.toFixed(6) + ' ' + toSymbol);
        }

        function calculateQuotation() {
            var fromOpt = $('#fromCurrencySelect').find(':selected');
            var toOpt = $('#toCurrencySelect').find(':selected');

            var fromId = $('#fromCurrencySelect').val();
            var toId = $('#toCurrencySelect').val();

            if (fromId === toId) {
                // Auto switch To currency if same
                $('#toCurrencySelect option').each(function() {
                    if ($(this).val() !== fromId) {
                        $('#toCurrencySelect').val($(this).val());
                        toOpt = $(this);
                        toId = $(this).val();
                        return false;
                    }
                });
            }

            var fromSymbol = fromOpt.data('symbol') || fromOpt.attr('data-symbol') || fromOpt.text().trim() || 'BTC';
            var toSymbol = toOpt.data('symbol') || toOpt.attr('data-symbol') || toOpt.text().trim() || 'USDT';

            updateSelectedBalances();

            var fromPrice = clientPrices[fromSymbol] || 1.0;
            var toPrice = clientPrices[toSymbol] || 1.0;
            var estimatedRate = fromPrice / toPrice;

            var amount = parseFloat($('#swapAmountInput').val()) || 0;
            var feePct = 0.10;

            // Instant Client Render (Zero Lag)
            var rateStr = '1 ' + fromSymbol + ' ≈ ' + (estimatedRate >= 1 ? estimatedRate.toFixed(4) : estimatedRate.toFixed(8)) + ' ' + toSymbol;
            $('#liveRateDisplay').text(rateStr);

            if (amount > 0) {
                var estReceive = amount * estimatedRate * (1 - (feePct / 100));
                var estCharge = amount * estimatedRate * (feePct / 100);
                $('#swapReceivePreview').val(estReceive >= 1 ? estReceive.toFixed(4) : estReceive.toFixed(8));
                $('#finalReceiveDisplay').text((estReceive >= 1 ? estReceive.toFixed(4) : estReceive.toFixed(8)) + ' ' + toSymbol);
                $('#liveFeeDisplay').text(estCharge.toFixed(4) + ' ' + toSymbol);
            } else {
                $('#swapReceivePreview').val('0.00');
                $('#finalReceiveDisplay').text('0.00 ' + toSymbol);
                $('#liveFeeDisplay').text('$0.00');
            }

            // Sync with backend calculation endpoint
            $.ajax({
                url: calculateUrl,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    from_currency: fromId,
                    to_currency: toId,
                    amount: amount > 0 ? amount : 1
                },
                success: function (res) {
                    if (res.success) {
                        currentRate = res.rate;
                        $('#liveRateDisplay').text(res.rate_display);
                        
                        if (amount > 0) {
                            $('#swapReceivePreview').val(res.final_amount);
                            $('#finalReceiveDisplay').text(res.final_amount + ' ' + res.to_symbol);
                            $('#liveFeeDisplay').text(res.charge + ' ' + res.to_symbol);
                        }
                    }
                }
            });
        }

        // Initialize balances and rate
        updateSelectedBalances();
        calculateQuotation();

        // Currency change handlers
        $('#fromCurrencySelect, #toCurrencySelect').on('change', function () {
            calculateQuotation();
        });

        // Amount input handler
        $('#swapAmountInput').on('input', function () {
            calculateQuotation();
        });

        // Quick Percentage buttons
        $('.quick-pct-btn').on('click', function () {
            var pct = parseFloat($(this).data('pct'));
            var fromOpt = $('#fromCurrencySelect').find(':selected');
            var balance = parseFloat(fromOpt.attr('data-balance')) || 0;
            var calcAmount = (balance * (pct / 100));

            $('#swapAmountInput').val(calcAmount > 0 ? (pct === 100 ? balance : calcAmount.toFixed(6)) : '0.00');
            calculateQuotation();
        });

        // Flip conversion direction button
        $('#flipDirectionBtn').on('click', function () {
            var fromId = $('#fromCurrencySelect').val();
            var toId = $('#toCurrencySelect').val();

            $('#fromCurrencySelect').val(toId);
            $('#toCurrencySelect').val(fromId);

            calculateQuotation();
        });

        // Submit Swap via AJAX
        $('#instantSwapForm').on('submit', function (e) {
            e.preventDefault();

            var submitBtn = $('#submitSwapBtn');
            var amount = parseFloat($('#swapAmountInput').val()) || 0;

            if (amount <= 0) {
                notify('error', 'Please enter a valid swap amount.');
                return;
            }

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Converting...');

            $.ajax({
                url: swapUrl,
                type: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    submitBtn.prop('disabled', false).html('<i class="las la-sync-alt fs-5"></i> <span>Convert & Swap Now</span>');

                    if (res.success) {
                        notify('success', res.message);
                        $('#swapAmountInput').val('');
                        $('#swapReceivePreview').val('');
                        $('#finalReceiveDisplay').text('0.00');

                        // Update cached balances in select elements
                        var fromOpt = $('#fromCurrencySelect option:selected');
                        var toOpt = $('#toCurrencySelect option:selected');

                        fromOpt.attr('data-balance', res.details.new_from_balance);
                        toOpt.attr('data-balance', res.details.new_to_balance);
                        updateSelectedBalances();

                        // Prepend new row to swap history table
                        var newRow = `
                            <tr>
                                <td class="ps-3 ps-sm-4 text-nowrap font-mono">
                                    <span class="text-white fw-medium">Just now</span>
                                    <small class="text-muted d-block">Instant</small>
                                </td>
                                <td>
                                    <span class="text--danger fw-bold font-mono">-${res.details.from_amount}</span>
                                    <span class="badge badge--dark ms-1">${res.details.from_currency}</span>
                                </td>
                                <td>
                                    <span class="text--success fw-bold font-mono">+${res.details.to_amount}</span>
                                    <span class="badge badge--dark ms-1">${res.details.to_currency}</span>
                                </td>
                                <td class="text-end font-mono text-white">
                                    1 ${res.details.from_currency} ≈ ${res.details.rate} ${res.details.to_currency}
                                </td>
                                <td class="text-end font-mono text-muted">
                                    ${res.details.fee}
                                </td>
                                <td class="text-center pe-3 pe-sm-4">
                                    <span class="badge badge--success-soft rounded-pill px-3 py-1 font-mono">
                                        <i class="las la-check-circle me-1"></i> SUCCESS
                                    </span>
                                </td>
                            </tr>
                        `;
                        $('#emptySwapRow').remove();
                        $('#swapHistoryTableBody').prepend(newRow);

                    } else {
                        notify('error', res.error || 'Conversion failed. Please try again.');
                    }
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).html('<i class="las la-sync-alt fs-5"></i> <span>Convert & Swap Now</span>');
                    var errorMsg = 'An error occurred during conversion.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    notify('error', errorMsg);
                }
            });
        });

        // Mobile Tabs Switcher
        function handleMobileSwapTabs() {
            if ($(window).width() < 768) {
                var activeTarget = $('.mobile-swap-tab-btn.active').data('target') || '#swapTerminalSection';
                $('.swap-content-section').addClass('d-none');
                $(activeTarget).removeClass('d-none');
            } else {
                $('.swap-content-section').removeClass('d-none');
            }
        }

        handleMobileSwapTabs();
        $(window).on('resize', handleMobileSwapTabs);

        $('.mobile-swap-tab-btn').on('click', function() {
            $('.mobile-swap-tab-btn').removeClass('active text-white').addClass('text-muted');
            $(this).addClass('active text-white').removeClass('text-muted');

            var target = $(this).data('target');
            $('.swap-content-section').addClass('d-none');
            $(target).removeClass('d-none');
        });
    })(jQuery);
</script>
@endpush