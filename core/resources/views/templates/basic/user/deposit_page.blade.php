@extends($activeTemplate . 'layouts.master')
@section('content')
<style>
.select2-dropdown { background-color: var(--vn-bg-elevated) !important; border: 1px solid var(--vn-border) !important; color: var(--vn-text-primary) !important; z-index: 999999 !important; min-width: 220px !important; box-shadow: 0 4px 12px rgba(0,0,0,0.5) !important; }
.select2-results__options { max-height: 400px !important; min-height: 250px !important; overflow-y: auto !important; padding: 0 !important; margin: 0 !important; display: block !important; }
.select2-results__option { padding: 12px 12px !important; font-size: 14px !important; background-color: var(--vn-bg-elevated) !important; color: var(--vn-text-primary) !important; border-bottom: 1px solid var(--vn-border) !important; min-height: 40px !important; display: block !important; }
.select2-container--default .select2-search--dropdown .select2-search__field { background-color: var(--vn-bg-primary) !important; color: var(--vn-text-primary) !important; border: 1px solid var(--vn-border) !important; border-radius: var(--vn-radius-sm) !important; padding: 6px !important; }
.select2-container--default .select2-results__option[aria-selected=true] { background-color: var(--vn-bg-card) !important; color: var(--vn-text-primary) !important; }
.select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--vn-bg-primary) !important; color: var(--vn-text-primary) !important; }
.select2-container--default .select2-selection--single { background-color: var(--vn-bg-elevated) !important; border: 1px solid var(--vn-border) !important; height: 40px !important; border-radius: var(--vn-radius-md) !important; }
.select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--vn-text-primary) !important; line-height: 40px !important; padding-left: 12px !important; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }
</style>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card custom--card">
                <div class="card-header card-header-bg">
                    <h5 class="card-title">{{ __($pageTitle) }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.deposit.insert') }}" method="post" class="deposit-form">
                        @csrf
                        <input type="hidden" name="currency">
                        <div class="form-group position-relative" id="currency_list_wrapper">
                            <label class="form-label">@lang('Select Currency')</label>
                            <x-currency-list :action="route('user.currency.all')" valueType="2" logCurrency="true" parent="currency_list_wrapper" class="form-control currency-list" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">@lang('Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" class="form--control form-control" name="amount" required>
                                <span class="input-group-text text-white deposit-currency-symbol"></span>
                            </div>
                        </div>
                        
                        <div class="form-group position-relative d-none" id="gateway-selection">
                            <label class="form-label">@lang('Payment Gateway')</label>
                            <select class="form-control form--control form-select select2" name="gateway" required data-minimum-results-for-search="-1">
                                <option selected disabled>@lang('Select Payment Gateway')</option>
                            </select>
                        </div>
                        
                        <div class="form-group position-relative">
                            <label class="form-label">@lang('Wallet Type')</label>
                            <select class="form-control form--control form-select" name="wallet_type" required>
                                <option value="" selected disabled>@lang('Select One')</option>
                                @foreach (gs('wallet_types') as $k => $walletType)
                                    @if (checkWalletConfiguration($k, 'deposit'))
                                        <option value="{{ $k }}">{{ __($walletType->title) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group preview-details d-none">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex flex-wrap justify-content-between">
                                    <span>@lang('Limit')</span>
                                    <span>
                                        <span class="min fw-bold">0</span>
                                        - <span class="max fw-bold">0</span>
                                        <span class="deposit-currency-symbol"></span>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex flex-wrap justify-content-between">
                                    <span>@lang('Charge')</span>
                                    <span>
                                        <span class="charge fw-bold">0</span>
                                        <span class="deposit-currency-symbol"></span>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex flex-wrap justify-content-between">
                                    <span> @lang('Payable')</span>
                                    <span>
                                        <span class="payable fw-bold">0</span>
                                        <span class="deposit-currency-symbol"></span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                        
                        <button class="deposit__button btn btn--base w-100" type="submit"> @lang('Submit') </button>
                    </form>
                    
                    <div class="p-5 text-center empty-gateway d-none">
                        <img src="{{ asset('assets/images/extra_images/no_money.png') }}">
                        <h6 class="mt-3">
                            @lang('No payment gateway available for ')
                            <span class="text--base deposit-currency-symbol"></span>
                            @lang('Currency')
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {
            let gateways = @json(\App\Models\GatewayCurrency::whereHas('method', function ($gate) { $gate->active(); })->with('method')->get());

            $('.currency-list').on('change', function() {
                let currency = $(this).val();
                $('input[name=currency]').val(currency);
                $('.deposit-currency-symbol').text(currency);
                
                if(!currency) return;

                let currencyGateways = gateways.filter(ele => ele.currency == currency);

                if (currencyGateways && currencyGateways.length) {
                    let gatewaysOption = "<option selected disabled> @lang('Select Payment Gateway')</option>";
                    $.each(currencyGateways, function(i, currencyGateway) {
                        gatewaysOption += `<option value="${currencyGateway.method_code}" data-gateway='${JSON.stringify(currencyGateway)}'>${currencyGateway.name}</option>`;
                    });

                    $(".empty-gateway").addClass('d-none');
                    $("form").removeClass('d-none');
                    $("#gateway-selection").removeClass('d-none');
                    $('select[name=gateway]').html(gatewaysOption);
                } else {
                    $(".empty-gateway").removeClass('d-none');
                    $("#gateway-selection").addClass('d-none');
                    $('.preview-details').addClass('d-none');
                }
            });

            $('select[name=gateway]').on('change', function() {
                if (!$(this).val()) {
                    $('.preview-details').addClass('d-none');
                    return false;
                }

                var resource = $('select[name=gateway] option:selected').data('gateway');
                var fixed_charge = parseFloat(resource.fixed_charge);
                var percent_charge = parseFloat(resource.percent_charge);

                $('.min').text(getAmount(resource.min_amount));
                $('.max').text(getAmount(resource.max_amount));

                var amount = parseFloat($('input[name=amount]').val());
                if (!amount) {
                    $('.preview-details').addClass('d-none');
                    return false;
                }

                $('.preview-details').removeClass('d-none');

                var charge = parseFloat(fixed_charge + (amount * percent_charge / 100));
                var payable = parseFloat((parseFloat(amount) + parseFloat(charge)));

                $('.charge').text(getAmount(charge));
                $('.payable').text(getAmount(payable));
            });

            $('input[name=amount]').on('input', function() {
                $('select[name=gateway]').change();
            });
            
            // Unbind dashboard canvas JS so it doesn't open the canvas when submitting
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
