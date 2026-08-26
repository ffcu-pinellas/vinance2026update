@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="deposit-page-container py-3">
    <!-- Top Bar with Back Button -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('user.fund') }}" class="btn btn-sm btn-outline--light px-3 py-2">
                <i class="las la-arrow-left me-1"></i>@lang('Back to Hub')
            </a>
            <div>
                <h4 class="text-white fw-bold mb-0">@lang('Deposit Money')</h4>
                <small class="text-muted">@lang('Add funds instantly to your trading or funding wallet')</small>
            </div>
        </div>
        <div class="d-none d-md-flex align-items-center gap-2 text-muted fs-7">
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                <i class="las la-lock me-1"></i>256-Bit SSL Encrypted
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-7 col-xl-8">
            <div class="card custom--card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('user.deposit.insert') }}" method="post" class="deposit-form" id="depositForm">
                        @csrf
                        
                        <!-- Step 1: Currency -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="step-badge">1</span>
                                <label class="form-label text-white fw-semibold mb-0">@lang('Select Currency')</label>
                            </div>
                            <!-- Searchable Coin Selector Trigger -->
                            <button type="button" class="btn btn-outline--light d-flex align-items-center justify-content-between gap-2 rounded-3 px-3 py-3 w-100 bg--dark-three border border-dark" id="openDepositCoinBtn">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="coin-avatar-circle bg--dark-two text--base fw-bold rounded-circle d-flex align-items-center justify-content-center border border-dark" style="width: 32px; height: 32px; font-size: 11px;" id="depositCoinBadge">
                                        {{ @$currencies->first()->symbol ?? 'USDT' }}
                                    </div>
                                    <div class="text-start">
                                        <span class="coin-symbol-label fw-bold font-mono text-white fs-6 d-block" id="depositCoinSymbolText">{{ @$currencies->first()->symbol ?? 'Select Currency' }}</span>
                                        <small class="text-muted" id="depositCoinNameText">{{ @$currencies->first()->name ?? 'Tap to browse' }}</small>
                                    </div>
                                </div>
                                <i class="las la-angle-down text-muted fs-5"></i>
                            </button>
                            <input type="hidden" name="currency" id="depositCurrencyInput" value="{{ @$currencies->first()->symbol ?? 'USDT' }}" required>
                        </div>

                        <!-- Step 2: Amount -->
                        <div class="form-section mb-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2 gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="step-badge">2</span>
                                    <label class="form-label text-white fw-semibold mb-0">@lang('Deposit Amount')</label>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-mono fs-12 px-2 py-1">
                                    <i class="las la-info-circle me-1"></i>@lang('Limit'): <span class="min text-white">0.00</span> - <span class="max text-white">0.00</span> <span class="deposit-currency-symbol text-white">USD</span>
                                </span>
                            </div>
                            <div class="input-group input-group-lg">
                                <input type="number" step="any" class="form--control form-control fs-5" name="amount" id="depositAmount" placeholder="0.00" required autocomplete="off">
                                <span class="input-group-text bg-dark border-secondary text-white fw-bold deposit-currency-symbol px-3">USD</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1 px-1 fs-12 text-muted">
                                <span>@lang('Min'): <strong class="text-white min">0.00</strong> <span class="deposit-currency-symbol">USD</span></span>
                                <span>@lang('Max'): <strong class="text-white max">0.00</strong> <span class="deposit-currency-symbol">USD</span></span>
                            </div>
                            
                            <!-- Quick Amount Preset Pills -->
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="button" class="quick-amt-btn" onclick="addAmount(50)">+50</button>
                                <button type="button" class="quick-amt-btn" onclick="addAmount(100)">+100</button>
                                <button type="button" class="quick-amt-btn" onclick="addAmount(500)">+500</button>
                                <button type="button" class="quick-amt-btn" onclick="addAmount(1000)">+1,000</button>
                                <button type="button" class="quick-amt-btn" onclick="addAmount(5000)">+5,000</button>
                                <button type="button" class="quick-amt-btn text-danger border-danger border-opacity-50" onclick="clearAmount()">@lang('Clear')</button>
                            </div>
                        </div>

                        <!-- Step 3: Payment Gateway Selection -->
                        <div class="form-section mb-4 d-none" id="gateway-selection">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="step-badge">3</span>
                                <label class="form-label text-white fw-semibold mb-0">@lang('Payment Gateway / Network')</label>
                            </div>
                            <button type="button" class="btn btn-outline--light d-flex align-items-center justify-content-between gap-2 rounded-3 px-3 py-3 w-100 bg--dark-three border border-dark" id="openGatewayModalBtn">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="coin-avatar-circle bg--dark-two text--base fw-bold rounded-circle d-flex align-items-center justify-content-center border border-dark" style="width: 32px; height: 32px; font-size: 14px;" id="gatewaySelectedIcon">
                                        <i class="las la-network-wired"></i>
                                    </div>
                                    <div class="text-start">
                                        <span class="fw-bold font-mono text-white fs-6 d-block" id="gatewaySelectedName">@lang('Select Payment Gateway')</span>
                                        <small class="text-muted" id="gatewaySelectedDetails">@lang('Tap to select network / provider')</small>
                                    </div>
                                </div>
                                <i class="las la-angle-down text-muted fs-5"></i>
                            </button>
                            <input type="hidden" name="gateway" id="depositGatewayInput" value="" required>
                        </div>

                        <!-- Step 4: Destination Wallet -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="step-badge">4</span>
                                <label class="form-label text-white fw-semibold mb-0">@lang('Destination Wallet Type')</label>
                            </div>
                            <div class="row g-2" id="walletTypePillsContainer">
                                @foreach (gs('wallet_types') as $k => $walletType)
                                    @if (checkWalletConfiguration($k, 'deposit'))
                                        <div class="col-6">
                                            <div class="wallet-type-card p-3 rounded-3 border border-dark bg--dark-three cursor-pointer d-flex align-items-center gap-3 {{ $loop->first ? 'active border--base' : '' }}" data-val="{{ $k }}" style="cursor: pointer; transition: all 0.2s ease;">
                                                <div class="wallet-icon-box text--base fs-3">
                                                    @if($k == 'spot')
                                                        <i class="las la-chart-line"></i>
                                                    @else
                                                        <i class="las la-wallet"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <strong class="text-white d-block text--small font-mono">{{ __($walletType->title) }}</strong>
                                                    <small class="text-muted" style="font-size: 11px;">{{ $k == 'spot' ? __('Trading & AI Bots') : __('P2P & Yield') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <input type="hidden" name="wallet_type" id="depositWalletTypeInput" value="{{ array_key_first((array)gs('wallet_types')) }}" required>
                        </div>

                        <button class="deposit__button btn btn--base btn-lg w-100 py-3 fw-bold fs-6 mt-3 shadow-sm" type="submit">
                            <i class="las la-check-circle me-1 fs-5"></i>@lang('Continue to Payment')
                        </button>
                    </form>

                    <div class="p-5 text-center empty-gateway d-none">
                        <img src="{{ asset('assets/images/extra_images/no_money.png') }}" style="max-height: 120px;" class="mb-3">
                        <h5 class="text-white fw-bold">@lang('No Gateway Available')</h5>
                        <p class="text-muted mb-0">
                            @lang('There are currently no active gateways configured for ')
                            <span class="text--base deposit-currency-symbol fw-bold"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary & Information Column -->
        <div class="col-lg-5 col-xl-4">
            <div class="card custom--card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 pb-3">
                    <h5 class="text-white fw-bold mb-0"><i class="las la-receipt text--base me-2"></i>@lang('Summary Breakdown')</h5>
                </div>
                <div class="card-body p-4">
                    <div class="summary-list">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">@lang('Selected Asset')</span>
                            <span class="text-white fw-bold deposit-currency-symbol">USD</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">@lang('Deposit Amount')</span>
                            <span class="text-white fw-bold"><span class="summary-amount">0.00</span> <span class="deposit-currency-symbol">USD</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">@lang('Deposit Limits')</span>
                            <span class="text-white-50"><span class="min">0.00</span> - <span class="max">0.00</span> <span class="deposit-currency-symbol">USD</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">@lang('Processing Fee')</span>
                            <span class="text-warning fw-bold"><span class="charge">0.00</span> <span class="deposit-currency-symbol">USD</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3 mt-2 bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-25">
                            <div>
                                <span class="text-white fw-bold d-block fs-6">@lang('Total Payable')</span>
                                <small class="text-muted">@lang('Amount to be billed')</small>
                            </div>
                            <span class="text-success fw-bolder fs-5"><span class="payable">0.00</span> <span class="deposit-currency-symbol">USD</span></span>
                        </div>
                    </div>

                    <!-- Security & Arrival Info -->
                    <div class="mt-4 p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2 text-white mb-2 fs-7 fw-semibold">
                            <i class="las la-info-circle text--base fs-5"></i>@lang('Deposit Notice')
                        </div>
                        <ul class="text-muted fs-8 ps-3 mb-0">
                            <li>@lang('Network confirmations usually take 1 to 5 minutes.')</li>
                            <li>@lang('Unique wallet QR codes will be generated on the next step.')</li>
                            <li>@lang('Make sure to send only the selected cryptocurrency asset.')</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <!-- Instant Searchable Deposit Coin Selection Modal -->
    <div class="modal fade" id="depositCoinModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg--dark-two border border-dark rounded-4 shadow-lg">
                <div class="modal-header border-bottom border-dark p-3 px-4">
                    <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                        <i class="las la-coins text--base"></i> @lang('Select Deposit Asset')
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 px-4">
                    <!-- Instant Search Input -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg--dark-three border-dark text-muted"><i class="las la-search fs-5"></i></span>
                        <input type="text" class="form-control bg--dark-three text-white border-dark font-mono shadow-none" id="depositCoinSearchInput" placeholder="@lang('Type coin symbol or name (e.g. BTC, ETH, USDT)...')" autocomplete="off">
                    </div>

                    <!-- Fast Coin List -->
                    <div class="coin-search-list-wrapper overflow-auto pe-1" style="max-height: 360px;">
                        <div class="list-group list-group-flush" id="depositCoinSearchList">
                            @if(isset($currencies) && count($currencies))
                                @foreach($currencies as $currency)
                                    <button type="button" class="list-group-item list-group-item-action bg-transparent text-white border-dark d-flex justify-content-between align-items-center py-2 px-2 rounded-3 deposit-coin-item-btn mb-1" 
                                        data-symbol="{{ $currency->symbol }}" 
                                        data-name="{{ $currency->name }}" 
                                        data-balance="{{ $currency->user_balance }}">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="coin-avatar-circle bg--dark-three text--base fw-bold rounded-circle d-flex align-items-center justify-content-center border border-dark" style="width: 36px; height: 36px; font-size: 12px;">
                                                {{ substr($currency->symbol, 0, 3) }}
                                            </div>
                                            <div class="text-start">
                                                <div class="fw-bold font-mono text-white fs-6">{{ $currency->symbol }}</div>
                                                <small class="text-muted">{{ $currency->name }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end font-mono">
                                            <span class="text-white d-block text--small">{{ number_format($currency->user_balance, 4) }}</span>
                                            <small class="text-muted" style="font-size: 11px;">@lang('Balance')</small>
                                        </div>
                                    </button>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instant Searchable Payment Gateway Modal -->
    <div class="modal fade" id="depositGatewayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg--dark-two border border-dark rounded-4 shadow-lg">
                <div class="modal-header border-bottom border-dark p-3 px-4">
                    <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                        <i class="las la-network-wired text--base"></i> @lang('Select Gateway / Network')
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 px-4">
                    <!-- Instant Search Input -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg--dark-three border-dark text-muted"><i class="las la-search fs-5"></i></span>
                        <input type="text" class="form-control bg--dark-three text-white border-dark font-mono shadow-none" id="depositGatewaySearchInput" placeholder="@lang('Search network or gateway...')" autocomplete="off">
                    </div>

                    <!-- Fast Gateway List -->
                    <div class="gateway-search-list-wrapper overflow-auto pe-1" style="max-height: 360px;">
                        <div class="list-group list-group-flush" id="depositGatewaySearchList">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
.step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background: rgba(0, 192, 135, 0.15);
    color: #00C087;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 700;
}
.quick-amt-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #8B94A5;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}
.quick-amt-btn:hover {
    background: rgba(0, 192, 135, 0.15);
    border-color: rgba(0, 192, 135, 0.4);
    color: #00C087;
}
.bg--dark-two { background: #0f172a !important; }
.bg--dark-three { background: #1e293b !important; }
.deposit-coin-item-btn:hover, .deposit-gateway-item-btn:hover {
    background-color: rgba(59, 130, 246, 0.15) !important;
    border-color: #3b82f6 !important;
}
.wallet-type-card.active {
    border-color: #00C087 !important;
    background: rgba(0, 192, 135, 0.08) !important;
}
</style>
@endpush

@push('script')
    <script>
        "use strict";
        let gateways = @json(\App\Models\GatewayCurrency::whereHas('method', function ($gate) { $gate->active(); })->with('method')->get());
        let userOverrides = @json($userDepositSettings ?? []);
        let currentCurrencyGateways = [];
        let currentSelectedGateway = null;

        function getGatewayConfig(g) {
            if (!g) return null;
            let override = userOverrides[g.id] || null;
            return {
                id: g.id,
                method_code: g.method_code,
                name: g.name,
                currency: g.currency,
                min_amount: override ? parseFloat(override.min_amount) : parseFloat(g.min_amount),
                max_amount: override ? parseFloat(override.max_amount) : parseFloat(g.max_amount),
                fixed_charge: override ? parseFloat(override.fixed_charge) : parseFloat(g.fixed_charge),
                percent_charge: override ? parseFloat(override.percent_charge) : parseFloat(g.percent_charge),
            };
        }

        function updateDepositSummary() {
            if (!currentSelectedGateway) return;

            let resource = currentSelectedGateway;
            let fixed_charge = parseFloat(resource.fixed_charge) || 0;
            let percent_charge = parseFloat(resource.percent_charge) || 0;

            $('.min').text(getAmount(resource.min_amount));
            $('.max').text(getAmount(resource.max_amount));

            let amount = parseFloat($('#depositAmount').val()) || 0;
            $('.summary-amount').text(getAmount(amount));

            let charge = parseFloat(fixed_charge + (amount * percent_charge / 100));
            let payable = parseFloat(amount + charge);

            $('.charge').text(getAmount(charge));
            $('.payable').text(getAmount(payable));
        }

        function addAmount(val) {
            let current = parseFloat($('#depositAmount').val()) || 0;
            $('#depositAmount').val(current + val).trigger('input');
        }

        function clearAmount() {
            $('#depositAmount').val('').trigger('input');
        }

        (function($) {
            function selectDepositGateway(gatewayObj) {
                if (!gatewayObj) return;
                currentSelectedGateway = getGatewayConfig(gatewayObj);

                $('#depositGatewayInput').val(currentSelectedGateway.id);
                $('#gatewaySelectedName').text(currentSelectedGateway.name);
                $('#gatewaySelectedDetails').text(`Min: ${getAmount(currentSelectedGateway.min_amount)} - Max: ${getAmount(currentSelectedGateway.max_amount)}`);

                updateDepositSummary();
            }

            function renderGatewayModalList(gatewaysList) {
                let html = '';
                $.each(gatewaysList, function(i, g) {
                    let cfg = getGatewayConfig(g);
                    html += `
                        <button type="button" class="list-group-item list-group-item-action bg-transparent text-white border-dark d-flex justify-content-between align-items-center py-2 px-2 rounded-3 deposit-gateway-item-btn mb-1" 
                            data-id="${cfg.id}" 
                            data-name="${cfg.name}">
                            <div class="d-flex align-items-center gap-3">
                                <div class="coin-avatar-circle bg--dark-three text--base fw-bold rounded-circle d-flex align-items-center justify-content-center border border-dark" style="width: 36px; height: 36px; font-size: 14px;">
                                    <i class="las la-network-wired"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold font-mono text-white fs-6">${cfg.name}</div>
                                    <small class="text-muted">Charge: ${getAmount(cfg.fixed_charge)} + ${cfg.percent_charge}%</small>
                                </div>
                            </div>
                            <div class="text-end font-mono">
                                <span class="text-white d-block text--small">${getAmount(cfg.min_amount)} - ${getAmount(cfg.max_amount)}</span>
                                <small class="text-muted" style="font-size: 11px;">@lang('Limits')</small>
                            </div>
                        </button>
                    `;
                });
                $('#depositGatewaySearchList').html(html);
            }

            function selectDepositCurrency(currencySymbol, currencyName) {
                currencySymbol = (currencySymbol || '').toString().trim().toUpperCase();
                $('#depositCurrencyInput').val(currencySymbol);
                $('#depositCoinBadge').text(currencySymbol.substring(0, 3));
                $('#depositCoinSymbolText').text(currencySymbol);
                $('#depositCoinNameText').text(currencyName || currencySymbol);
                $('.deposit-currency-symbol').text(currencySymbol || 'USD');

                currentCurrencyGateways = gateways.filter(ele => (ele.currency || '').toString().trim().toUpperCase() === currencySymbol);

                if (currentCurrencyGateways && currentCurrencyGateways.length) {
                    $(".empty-gateway").addClass('d-none');
                    $("#depositForm").removeClass('d-none');
                    $("#gateway-selection").removeClass('d-none');
                    
                    renderGatewayModalList(currentCurrencyGateways);
                    selectDepositGateway(currentCurrencyGateways[0]);
                } else {
                    currentSelectedGateway = null;
                    $(".empty-gateway").removeClass('d-none');
                    $("#gateway-selection").addClass('d-none');
                    $('.min').text('0.00');
                    $('.max').text('0.00');
                }
            }

            // Open Coin Modal
            $('#openDepositCoinBtn').on('click', function() {
                $('#depositCoinSearchInput').val('');
                $('#depositCoinSearchList .deposit-coin-item-btn').removeClass('d-none');
                $('#depositCoinModal').modal('show');
                setTimeout(function() {
                    $('#depositCoinSearchInput').focus();
                }, 300);
            });

            // Instant Coin Search
            $('#depositCoinSearchInput').on('input keyup', function() {
                let q = $(this).val().toLowerCase().trim();
                $('#depositCoinSearchList .deposit-coin-item-btn').each(function() {
                    let sym = $(this).data('symbol').toString().toLowerCase();
                    let name = ($(this).data('name') || '').toString().toLowerCase();
                    if (sym.indexOf(q) > -1 || name.indexOf(q) > -1) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            });

            // Select Coin Item
            $(document).on('click', '.deposit-coin-item-btn', function() {
                let sym = $(this).data('symbol');
                let name = $(this).data('name');
                selectDepositCurrency(sym, name);
                $('#depositCoinModal').modal('hide');
            });

            // Open Gateway Modal
            $('#openGatewayModalBtn').on('click', function() {
                $('#depositGatewaySearchInput').val('');
                $('#depositGatewaySearchList .deposit-gateway-item-btn').removeClass('d-none');
                $('#depositGatewayModal').modal('show');
                setTimeout(function() {
                    $('#depositGatewaySearchInput').focus();
                }, 300);
            });

            // Instant Gateway Search
            $('#depositGatewaySearchInput').on('input keyup', function() {
                let q = $(this).val().toLowerCase().trim();
                $('#depositGatewaySearchList .deposit-gateway-item-btn').each(function() {
                    let name = ($(this).data('name') || '').toString().toLowerCase();
                    if (name.indexOf(q) > -1) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            });

            // Select Gateway Item
            $(document).on('click', '.deposit-gateway-item-btn', function() {
                let gwId = $(this).data('id');
                let gwObj = gateways.find(ele => ele.id == gwId);
                if (gwObj) {
                    selectDepositGateway(gwObj);
                }
                $('#depositGatewayModal').modal('hide');
            });

            // Wallet Type Pills
            $(document).on('click', '.wallet-type-card', function() {
                $('.wallet-type-card').removeClass('active border--base');
                $(this).addClass('active border--base');
                $('#depositWalletTypeInput').val($(this).data('val'));
            });

            // Auto-select USDT if available, otherwise first currency
            let defaultSym = "{{ @$currencies->where('symbol', 'USDT')->first()->symbol ?? @$currencies->first()->symbol ?? 'USDT' }}";
            let defaultName = "{{ @$currencies->where('symbol', 'USDT')->first()->name ?? @$currencies->first()->name ?? 'Tether' }}";
            selectDepositCurrency(defaultSym, defaultName);

            $('#depositAmount').on('input keyup change', function() {
                updateDepositSummary();
            });
            
            $(document).off('submit', '.deposit-form');
        })(jQuery);
    </script>
@endpush
