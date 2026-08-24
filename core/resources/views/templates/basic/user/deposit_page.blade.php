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
                        <input type="hidden" name="currency">
                        
                        <!-- Step 1: Currency -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="step-badge">1</span>
                                <label class="form-label text-white fw-semibold mb-0">@lang('Select Currency')</label>
                            </div>
                            <div class="position-relative" id="currency_list_wrapper">
                                <x-currency-list :action="route('user.currency.all')" valueType="2" parent="currency_list_wrapper" class="form-control currency-list" gatewayType="deposit" />
                            </div>
                        </div>

                        <!-- Step 2: Amount -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="step-badge">2</span>
                                    <label class="form-label text-white fw-semibold mb-0">@lang('Deposit Amount')</label>
                                </div>
                            </div>
                            <div class="input-group input-group-lg">
                                <input type="number" step="any" class="form--control form-control fs-5" name="amount" id="depositAmount" placeholder="0.00" required autocomplete="off">
                                <span class="input-group-text bg-dark border-secondary text-white fw-bold deposit-currency-symbol px-3">USD</span>
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
                            <select class="form-control form--control form-select select2" name="gateway" required data-minimum-results-for-search="-1">
                                <option selected disabled>@lang('Select Payment Gateway')</option>
                            </select>
                        </div>

                        <!-- Step 4: Destination Wallet -->
                        <div class="form-section mb-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="step-badge">4</span>
                                <label class="form-label text-white fw-semibold mb-0">@lang('Destination Wallet Type')</label>
                            </div>
                            <select class="form-control form--control form-select" name="wallet_type" required>
                                <option value="" selected disabled>@lang('Select Wallet Type')</option>
                                @foreach (gs('wallet_types') as $k => $walletType)
                                    @if (checkWalletConfiguration($k, 'deposit'))
                                        <option value="{{ $k }}" {{ $loop->first ? 'selected' : '' }}>{{ __($walletType->title) }}</option>
                                    @endif
                                @endforeach
                            </select>
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
.select2-dropdown { background-color: var(--vn-bg-elevated) !important; border: 1px solid var(--vn-border) !important; color: var(--vn-text-primary) !important; z-index: 999999 !important; min-width: 220px !important; box-shadow: 0 4px 12px rgba(0,0,0,0.5) !important; }
.select2-results__options { max-height: 400px !important; min-height: 250px !important; overflow-y: auto !important; padding: 0 !important; margin: 0 !important; display: block !important; }
.select2-results__option { padding: 12px 12px !important; font-size: 14px !important; background-color: var(--vn-bg-elevated) !important; color: var(--vn-text-primary) !important; border-bottom: 1px solid var(--vn-border) !important; min-height: 40px !important; display: block !important; }
.select2-container--default .select2-search--dropdown .select2-search__field { background-color: var(--vn-bg-primary) !important; color: var(--vn-text-primary) !important; border: 1px solid var(--vn-border) !important; border-radius: var(--vn-radius-sm) !important; padding: 6px !important; }
.select2-container--default .select2-results__option[aria-selected=true] { background-color: var(--vn-bg-card) !important; color: var(--vn-text-primary) !important; }
.select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--vn-bg-primary) !important; color: var(--vn-text-primary) !important; }
.select2-container--default .select2-selection--single { background-color: var(--vn-bg-elevated) !important; border: 1px solid var(--vn-border) !important; height: 45px !important; border-radius: var(--vn-radius-md) !important; display: flex !important; align-items: center !important; }
.select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--vn-text-primary) !important; line-height: 45px !important; padding-left: 12px !important; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 45px !important; }
</style>
@endpush

@push('script')
    <script>
        "use strict";
        let gateways = @json(\App\Models\GatewayCurrency::whereHas('method', function ($gate) { $gate->active(); })->with('method')->get());

        function addAmount(val) {
            let current = parseFloat($('#depositAmount').val()) || 0;
            $('#depositAmount').val(current + val).trigger('input');
        }

        function clearAmount() {
            $('#depositAmount').val('').trigger('input');
        }

        (function($) {
            $('.currency-list').on('change', function() {
                let currency = $(this).val();
                $('input[name=currency]').val(currency);
                $('.deposit-currency-symbol').text(currency || 'USD');
                
                if(!currency) return;

                let currencyGateways = gateways.filter(ele => ele.currency == currency);

                if (currencyGateways && currencyGateways.length) {
                    let gatewaysOption = "<option selected disabled> @lang('Select Payment Gateway')</option>";
                    $.each(currencyGateways, function(i, currencyGateway) {
                        gatewaysOption += `<option value="${currencyGateway.method_code}" data-gateway='${JSON.stringify(currencyGateway)}'>${currencyGateway.name}</option>`;
                    });

                    $(".empty-gateway").addClass('d-none');
                    $("#depositForm").removeClass('d-none');
                    $("#gateway-selection").removeClass('d-none');
                    $('select[name=gateway]').html(gatewaysOption);
                    
                    if (currencyGateways.length === 1) {
                        $('select[name=gateway]').val(currencyGateways[0].method_code).trigger('change');
                    }
                } else {
                    $(".empty-gateway").removeClass('d-none');
                    $("#gateway-selection").addClass('d-none');
                }
            });

            $('select[name=gateway]').on('change', function() {
                if (!$(this).val()) {
                    return false;
                }

                var resource = $('select[name=gateway] option:selected').data('gateway');
                if (!resource) return;

                var fixed_charge = parseFloat(resource.fixed_charge) || 0;
                var percent_charge = parseFloat(resource.percent_charge) || 0;

                $('.min').text(getAmount(resource.min_amount));
                $('.max').text(getAmount(resource.max_amount));

                var amount = parseFloat($('#depositAmount').val()) || 0;
                $('.summary-amount').text(getAmount(amount));

                var charge = parseFloat(fixed_charge + (amount * percent_charge / 100));
                var payable = parseFloat(amount + charge);

                $('.charge').text(getAmount(charge));
                $('.payable').text(getAmount(payable));
            });

            $('#depositAmount').on('input', function() {
                var amount = parseFloat($(this).val()) || 0;
                $('.summary-amount').text(getAmount(amount));
                $('select[name=gateway]').trigger('change');
            });
            
            $(document).off('submit', '.deposit-form');
        })(jQuery);
    </script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush
