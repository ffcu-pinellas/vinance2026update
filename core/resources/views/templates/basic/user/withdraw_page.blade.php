@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="withdraw-page-container py-3">
    <!-- Top Bar with Back Button -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('user.fund') }}" class="btn btn-sm btn-outline--light px-3 py-2">
                <i class="las la-arrow-left me-1"></i>@lang('Back to Hub')
            </a>
            <div>
                <h4 class="text-white fw-bold mb-0">@lang('Withdraw Money')</h4>
                <small class="text-muted">@lang('Transfer funds safely to your external destination')</small>
            </div>
        </div>
        <div class="d-none d-md-flex align-items-center gap-2 text-muted fs-7">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                <i class="las la-shield-alt me-1"></i>@lang('2FA Protected Payout')
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-7 col-xl-8">
            <div class="card custom--card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('user.withdraw.money') }}" method="post" class="withdraw-form" id="withdrawForm">
                        @csrf
                        <input type="hidden" name="currency">
                        
                        <!-- Step 1: Currency -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="step-badge">1</span>
                                <label class="form-label text-white fw-semibold mb-0">@lang('Select Currency')</label>
                            </div>
                            <!-- Searchable Coin Selector Trigger -->
                            <button type="button" class="btn btn-outline--light d-flex align-items-center justify-content-between gap-2 rounded-3 px-3 py-3 w-100 bg--dark-three border border-dark" id="openWithdrawCoinBtn">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="coin-avatar-circle bg--dark-two text--base fw-bold rounded-circle d-flex align-items-center justify-content-center border border-dark" style="width: 32px; height: 32px; font-size: 11px;" id="withdrawCoinBadge">
                                        {{ @$currencies->first()->symbol ?? 'USDT' }}
                                    </div>
                                    <div class="text-start">
                                        <span class="coin-symbol-label fw-bold font-mono text-white fs-6 d-block" id="withdrawCoinSymbolText">{{ @$currencies->first()->symbol ?? 'Select Currency' }}</span>
                                        <small class="text-muted" id="withdrawCoinNameText">{{ @$currencies->first()->name ?? 'Tap to browse' }}</small>
                                    </div>
                                </div>
                                <i class="las la-angle-down text-muted fs-5"></i>
                            </button>
                            <input type="hidden" name="currency" id="withdrawCurrencyInput" value="{{ @$currencies->first()->symbol ?? 'USDT' }}" required>
                        </div>

                        <!-- Step 2: Amount -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="step-badge">2</span>
                                    <label class="form-label text-white fw-semibold mb-0">@lang('Withdraw Amount')</label>
                                </div>
                            </div>
                            <div class="input-group input-group-lg">
                                <input type="number" step="any" class="form--control form-control fs-5" name="amount" id="withdrawAmount" placeholder="0.00" required autocomplete="off">
                                <span class="input-group-text bg-dark border-secondary text-white fw-bold withdraw-currency-symbol px-3">USD</span>
                            </div>
                            
                            <!-- Quick Amount Preset Pills -->
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="button" class="quick-amt-btn" onclick="addWithdrawAmount(50)">+50</button>
                                <button type="button" class="quick-amt-btn" onclick="addWithdrawAmount(100)">+100</button>
                                <button type="button" class="quick-amt-btn" onclick="addWithdrawAmount(500)">+500</button>
                                <button type="button" class="quick-amt-btn" onclick="addWithdrawAmount(1000)">+1,000</button>
                                <button type="button" class="quick-amt-btn" onclick="addWithdrawAmount(5000)">+5,000</button>
                                <button type="button" class="quick-amt-btn text-danger border-danger border-opacity-50" onclick="clearWithdrawAmount()">@lang('Clear')</button>
                            </div>
                        </div>

                        <!-- Step 3: Withdraw Method Selection -->
                        <div class="form-section mb-4 d-none" id="method-selection">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="step-badge">3</span>
                                <label class="form-label text-white fw-semibold mb-0">@lang('Withdrawal Method')</label>
                            </div>
                            <button type="button" class="btn btn-outline--light d-flex align-items-center justify-content-between gap-2 rounded-3 px-3 py-3 w-100 bg--dark-three border border-dark" id="openWithdrawMethodBtn">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="coin-avatar-circle bg--dark-two text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center border border-dark" style="width: 32px; height: 32px; font-size: 14px;" id="withdrawMethodSelectedIcon">
                                        <i class="las la-money-bill-wave"></i>
                                    </div>
                                    <div class="text-start">
                                        <span class="fw-bold font-mono text-white fs-6 d-block" id="withdrawMethodSelectedName">@lang('Select Withdraw Method')</span>
                                        <small class="text-muted" id="withdrawMethodSelectedDetails">@lang('Tap to select payout provider / network')</small>
                                    </div>
                                </div>
                                <i class="las la-angle-down text-muted fs-5"></i>
                            </button>
                            <select class="d-none" name="method_code" id="withdrawMethodSelect" required>
                                <option selected disabled value="">@lang('Select Withdraw Method')</option>
                            </select>
                        </div>

                        <!-- Step 4: Source Wallet -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="step-badge">4</span>
                                <label class="form-label text-white fw-semibold mb-0">@lang('Source Wallet Type')</label>
                            </div>
                            <div class="row g-2" id="withdrawWalletTypePillsContainer">
                                @foreach (gs('wallet_types') as $k => $walletType)
                                    @if (checkWalletConfiguration($k, 'withdraw'))
                                        <div class="col-6">
                                            <div class="wallet-type-card p-3 rounded-3 border border-dark bg--dark-three cursor-pointer d-flex align-items-center gap-3 {{ $loop->first ? 'active border-primary' : '' }}" data-val="{{ $k }}" style="cursor: pointer; transition: all 0.2s ease;">
                                                <div class="wallet-icon-box text-primary fs-3">
                                                    @if($k == 'spot')
                                                        <i class="las la-chart-line"></i>
                                                    @else
                                                        <i class="las la-wallet"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <strong class="text-white d-block text--small font-mono">{{ __($walletType->title) }}</strong>
                                                    <small class="text-muted" style="font-size: 11px;">{{ $k == 'spot' ? __('Trading & Spot Balances') : __('Funding & P2P Balances') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <input type="hidden" name="wallet_type" id="withdrawWalletTypeInput" value="{{ array_key_first((array)gs('wallet_types')) }}" required>
                        </div>

                        <button class="deposit__button btn btn--base btn-lg w-100 py-3 fw-bold fs-6 mt-3 shadow-sm" type="submit">
                            <i class="las la-arrow-circle-right me-1 fs-5"></i>@lang('Continue to Verification')
                        </button>
                    </form>

                    <div class="p-5 text-center empty-gateway d-none">
                        <img src="{{ asset('assets/images/extra_images/no_money.png') }}" style="max-height: 120px;" class="mb-3">
                        <h5 class="text-white fw-bold">@lang('No Withdrawal Method Available')</h5>
                        <p class="text-muted mb-0">
                            @lang('There are currently no active payout methods configured for ')
                            <span class="text--base withdraw-currency-symbol fw-bold"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawal Summary & Information Column -->
        <div class="col-lg-5 col-xl-4">
            <div class="card custom--card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 pb-3">
                    <h5 class="text-white fw-bold mb-0"><i class="las la-receipt text--base me-2"></i>@lang('Withdrawal Breakdown')</h5>
                </div>
                <div class="card-body p-4">
                    <div class="summary-list">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">@lang('Selected Asset')</span>
                            <span class="text-white fw-bold withdraw-currency-symbol">USD</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">@lang('Withdraw Amount')</span>
                            <span class="text-white fw-bold"><span class="summary-amount">0.00</span> <span class="withdraw-currency-symbol">USD</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">@lang('Payout Limits')</span>
                            <span class="text-white-50"><span class="min">0.00</span> - <span class="max">0.00</span> <span class="withdraw-currency-symbol">USD</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">@lang('Processing Fee')</span>
                            <span class="text-warning fw-bold"><span class="charge">0.00</span> <span class="withdraw-currency-symbol">USD</span></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3 mt-2 bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-25">
                            <div>
                                <span class="text-white fw-bold d-block fs-6">@lang('Net Payout')</span>
                                <small class="text-muted">@lang('Amount you will receive')</small>
                            </div>
                            <span class="text-primary fw-bolder fs-5"><span class="payable">0.00</span> <span class="withdraw-currency-symbol">USD</span></span>
                        </div>
                    </div>

                    <!-- Security & Timing Info -->
                    <div class="mt-4 p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2 text-white mb-2 fs-7 fw-semibold">
                            <i class="las la-shield-alt text--base fs-5"></i>@lang('Security & Processing')
                        </div>
                        <ul class="text-muted fs-8 ps-3 mb-0">
                            <li>@lang('Withdrawals undergo multi-sig automated compliance checks.')</li>
                            <li>@lang('Destination details and 2FA will be verified on the next step.')</li>
                            <li>@lang('Ensure your external wallet address or account is 100% accurate.')</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <!-- Instant Searchable Withdraw Coin Selection Modal -->
    <div class="modal fade" id="withdrawCoinModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg--dark-two border border-dark rounded-4 shadow-lg">
                <div class="modal-header border-bottom border-dark p-3 px-4">
                    <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                        <i class="las la-coins text--base"></i> @lang('Select Withdraw Asset')
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 px-4">
                    <!-- Instant Search Input -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg--dark-three border-dark text-muted"><i class="las la-search fs-5"></i></span>
                        <input type="text" class="form-control bg--dark-three text-white border-dark font-mono shadow-none" id="withdrawCoinSearchInput" placeholder="@lang('Type coin symbol or name (e.g. BTC, ETH, USDT)...')" autocomplete="off">
                    </div>

                    <!-- Fast Coin List -->
                    <div class="coin-search-list-wrapper overflow-auto pe-1" style="max-height: 360px;">
                        <div class="list-group list-group-flush" id="withdrawCoinSearchList">
                            @if(isset($currencies) && count($currencies))
                                @foreach($currencies as $currency)
                                    <button type="button" class="list-group-item list-group-item-action bg-transparent text-white border-dark d-flex justify-content-between align-items-center py-2 px-2 rounded-3 withdraw-coin-item-btn mb-1" 
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
                                            <small class="text-muted" style="font-size: 11px;">@lang('Available')</small>
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

    <!-- Instant Searchable Withdraw Method Modal -->
    <div class="modal fade" id="withdrawMethodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg--dark-two border border-dark rounded-4 shadow-lg">
                <div class="modal-header border-bottom border-dark p-3 px-4">
                    <h5 class="modal-title text-white fw-bold d-flex align-items-center gap-2">
                        <i class="las la-money-bill-wave text-primary"></i> @lang('Select Withdrawal Method')
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 px-4">
                    <!-- Instant Search Input -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg--dark-three border-dark text-muted"><i class="las la-search fs-5"></i></span>
                        <input type="text" class="form-control bg--dark-three text-white border-dark font-mono shadow-none" id="withdrawMethodSearchInput" placeholder="@lang('Search method name or provider...')" autocomplete="off">
                    </div>

                    <!-- Fast Method List -->
                    <div class="method-search-list-wrapper overflow-auto pe-1" style="max-height: 360px;">
                        <div class="list-group list-group-flush" id="withdrawMethodSearchList">
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
    background: rgba(56, 97, 251, 0.15);
    color: #3861FB;
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
    background: rgba(56, 97, 251, 0.15);
    border-color: rgba(56, 97, 251, 0.4);
    color: #3861FB;
}
.bg--dark-two { background: #0f172a !important; }
.bg--dark-three { background: #1e293b !important; }
.withdraw-coin-item-btn:hover, .withdraw-method-item-btn:hover {
    background-color: rgba(56, 97, 251, 0.15) !important;
    border-color: #3861FB !important;
}
.wallet-type-card.active {
    border-color: #3861FB !important;
    background: rgba(56, 97, 251, 0.08) !important;
}
</style>
@endpush

@push('script')
    <script>
        "use strict";
        let methods = @json(\App\Models\WithdrawMethod::where('status', \App\Constants\Status::ENABLE)->get());
        let userOverrides = @json($userWithdrawSettings ?? []);
        let currentCurrencyMethods = [];
        let currentSelectedMethod = null;

        function getMethodConfig(m) {
            if (!m) return null;
            let override = userOverrides[m.id] || null;
            return {
                id: m.id,
                name: m.name,
                currency: m.currency,
                min_limit: override ? parseFloat(override.min_amount) : parseFloat(m.min_limit),
                max_limit: override ? parseFloat(override.max_amount) : parseFloat(m.max_limit),
                fixed_charge: override ? parseFloat(override.fixed_charge) : parseFloat(m.fixed_charge),
                percent_charge: override ? parseFloat(override.percent_charge) : parseFloat(m.percent_charge),
            };
        }

        function updateWithdrawSummary() {
            if (!currentSelectedMethod) return;

            let resource = currentSelectedMethod;
            let fixed_charge = parseFloat(resource.fixed_charge) || 0;
            let percent_charge = parseFloat(resource.percent_charge) || 0;

            $('.min').text(getAmount(resource.min_limit));
            $('.max').text(getAmount(resource.max_limit));

            let amount = parseFloat($('#withdrawAmount').val()) || 0;
            $('.summary-amount').text(getAmount(amount));

            let charge = parseFloat(fixed_charge + (amount * percent_charge / 100));
            let receiving = Math.max(0, parseFloat(amount - charge));

            $('.charge').text(getAmount(charge));
            $('.receiving').text(getAmount(receiving));
        }

        function addWithdrawAmount(val) {
            let current = parseFloat($('#withdrawAmount').val()) || 0;
            $('#withdrawAmount').val(current + val).trigger('input');
        }

        function clearWithdrawAmount() {
            $('#withdrawAmount').val('').trigger('input');
        }

        (function($) {
            function selectWithdrawMethod(methodObj) {
                if (!methodObj) return;
                currentSelectedMethod = getMethodConfig(methodObj);

                $('#withdrawMethodSelect').html(`<option value="${currentSelectedMethod.id}" selected>${currentSelectedMethod.name}</option>`);
                $('#withdrawMethodSelectedName').text(currentSelectedMethod.name);
                $('#withdrawMethodSelectedDetails').text(`Min: ${getAmount(currentSelectedMethod.min_limit)} - Max: ${getAmount(currentSelectedMethod.max_limit)}`);

                updateWithdrawSummary();
            }

            function renderMethodModalList(methodsList) {
                let html = '';
                $.each(methodsList, function(i, m) {
                    let cfg = getMethodConfig(m);
                    html += `
                        <button type="button" class="list-group-item list-group-item-action bg-transparent text-white border-dark d-flex justify-content-between align-items-center py-2 px-2 rounded-3 withdraw-method-item-btn mb-1" 
                            data-id="${cfg.id}" 
                            data-name="${cfg.name}" 
                            data-method='${JSON.stringify(m)}'>
                            <div class="d-flex align-items-center gap-3">
                                <div class="coin-avatar-circle bg--dark-three text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center border border-dark" style="width: 36px; height: 36px; font-size: 14px;">
                                    <i class="las la-money-bill-wave"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold font-mono text-white fs-6">${cfg.name}</div>
                                    <small class="text-muted">Charge: ${getAmount(cfg.fixed_charge)} + ${cfg.percent_charge}%</small>
                                </div>
                            </div>
                            <div class="text-end font-mono">
                                <span class="text-white d-block text--small">${getAmount(cfg.min_limit)} - ${getAmount(cfg.max_limit)}</span>
                                <small class="text-muted" style="font-size: 11px;">@lang('Limits')</small>
                            </div>
                        </button>
                    `;
                });
                $('#withdrawMethodSearchList').html(html);
            }

            function selectWithdrawCurrency(currencySymbol, currencyName) {
                currencySymbol = (currencySymbol || '').toString().trim().toUpperCase();
                $('#withdrawCurrencyInput').val(currencySymbol);
                $('#withdrawCoinBadge').text(currencySymbol.substring(0, 3));
                $('#withdrawCoinSymbolText').text(currencySymbol);
                $('#withdrawCoinNameText').text(currencyName || currencySymbol);
                $('.withdraw-currency-symbol').text(currencySymbol || 'USD');
                
                currentCurrencyMethods = methods.filter(ele => (ele.currency || '').toString().trim().toUpperCase() === currencySymbol);

                if (currentCurrencyMethods && currentCurrencyMethods.length) {
                    $(".empty-gateway").addClass('d-none');
                    $("#withdrawForm").removeClass('d-none');
                    $("#method-selection").removeClass('d-none');
                    
                    renderMethodModalList(currentCurrencyMethods);
                    selectWithdrawMethod(currentCurrencyMethods[0]);
                } else {
                    currentSelectedMethod = null;
                    $(".empty-gateway").removeClass('d-none');
                    $("#method-selection").addClass('d-none');
                    $('.min').text('0.00');
                    $('.max').text('0.00');
                }
            }

            // Open Coin Modal
            $('#openWithdrawCoinBtn').on('click', function() {
                $('#withdrawCoinSearchInput').val('');
                $('#withdrawCoinSearchList .withdraw-coin-item-btn').removeClass('d-none');
                $('#withdrawCoinModal').modal('show');
                setTimeout(function() {
                    $('#withdrawCoinSearchInput').focus();
                }, 300);
            });

            // Instant Coin Search
            $('#withdrawCoinSearchInput').on('input keyup', function() {
                let q = $(this).val().toLowerCase().trim();
                $('#withdrawCoinSearchList .withdraw-coin-item-btn').each(function() {
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
            $(document).on('click', '.withdraw-coin-item-btn', function() {
                let sym = $(this).data('symbol');
                let name = $(this).data('name');
                selectWithdrawCurrency(sym, name);
                $('#withdrawCoinModal').modal('hide');
            });

            // Open Method Modal
            $('#openWithdrawMethodBtn').on('click', function() {
                $('#withdrawMethodSearchInput').val('');
                $('#withdrawMethodSearchList .withdraw-method-item-btn').removeClass('d-none');
                $('#withdrawMethodModal').modal('show');
                setTimeout(function() {
                    $('#withdrawMethodSearchInput').focus();
                }, 300);
            });

            // Instant Method Search
            $('#withdrawMethodSearchInput').on('input keyup', function() {
                let q = $(this).val().toLowerCase().trim();
                $('#withdrawMethodSearchList .withdraw-method-item-btn').each(function() {
                    let name = ($(this).data('name') || '').toString().toLowerCase();
                    if (name.indexOf(q) > -1) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            });

            // Select Method Item
            $(document).on('click', '.withdraw-method-item-btn', function() {
                let mObj = $(this).data('method');
                selectWithdrawMethod(mObj);
                $('#withdrawMethodModal').modal('hide');
            });

            // Wallet Type Pills
            $(document).on('click', '#withdrawWalletTypePillsContainer .wallet-type-card', function() {
                $('#withdrawWalletTypePillsContainer .wallet-type-card').removeClass('active border-primary');
                $(this).addClass('active border-primary');
                $('#withdrawWalletTypeInput').val($(this).data('val'));
            });

            // Auto-select USDT if available, otherwise first currency
            let defaultSym = "{{ @$currencies->where('symbol', 'USDT')->first()->symbol ?? @$currencies->first()->symbol ?? 'USDT' }}";
            let defaultName = "{{ @$currencies->where('symbol', 'USDT')->first()->name ?? @$currencies->first()->name ?? 'Tether' }}";
            selectWithdrawCurrency(defaultSym, defaultName);

            $('#withdrawAmount').on('input keyup change', function() {
                updateWithdrawSummary();
            });
            
            $(document).off('submit', '.withdraw-form');
        })(jQuery);
    </script>
@endpush
