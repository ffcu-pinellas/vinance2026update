@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card custom--card">
                <div class="card-header card-header-bg">
                    <h5 class="card-title">{{ __($pageTitle) }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.withdraw.money') }}" method="post" class="withdraw-form">
                        @csrf
                        <input type="hidden" name="currency">
                        <div class="form-group">
                            <label class="form-label">@lang('Select Currency')</label>
                            <x-currency-list :action="route('user.currency.all')" valueType="2" logCurrency="true" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">@lang('Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" class="form--control form-control" name="amount" required>
                                <span class="input-group-text text-white withdraw-currency-symbol"></span>
                            </div>
                        </div>
                        
                        <div class="form-group position-relative d-none" id="method-selection">
                            <label class="form-label">@lang('Withdraw Method')</label>
                            <select class="form-control form--control form-select select2" name="method_code" required data-minimum-results-for-search="-1">
                                <option selected disabled>@lang('Select Withdraw Method')</option>
                            </select>
                        </div>
                        
                        <div class="form-group position-relative">
                            <label class="form-label">@lang('Wallet Type')</label>
                            <select class="form-control form--control form-select" name="wallet_type" required>
                                <option value="" selected disabled>@lang('Select One')</option>
                                @foreach (gs('wallet_types') as $k => $walletType)
                                    @if (checkWalletConfiguration($k, 'withdraw'))
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
                                        <span class="withdraw-currency-symbol"></span>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex flex-wrap justify-content-between">
                                    <span>@lang('Charge')</span>
                                    <span>
                                        <span class="charge fw-bold">0</span>
                                        <span class="withdraw-currency-symbol"></span>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex flex-wrap justify-content-between">
                                    <span> @lang('Payable')</span>
                                    <span>
                                        <span class="payable fw-bold">0</span>
                                        <span class="withdraw-currency-symbol"></span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                        
                        <button class="deposit__button btn btn--base w-100" type="submit"> @lang('Submit') </button>
                    </form>
                    
                    <div class="p-5 text-center empty-gateway d-none">
                        <img src="{{ asset('assets/images/extra_images/no_money.png') }}">
                        <h6 class="mt-3">
                            @lang('No withdraw method available for ')
                            <span class="text--base withdraw-currency-symbol"></span>
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
            let methods = @json(\App\Models\WithdrawMethod::where('status', \App\Constants\Status::ENABLE)->get());

            $('.currency-list').on('change', function() {
                let currency = $(this).val();
                $('input[name=currency]').val(currency);
                $('.withdraw-currency-symbol').text(currency);
                
                if(!currency) return;

                let currencyMethods = methods.filter(ele => ele.currency == currency);

                if (currencyMethods && currencyMethods.length) {
                    let methodsOption = "<option selected disabled> @lang('Select Withdraw Method')</option>";
                    $.each(currencyMethods, function(i, currencyMethod) {
                        methodsOption += `<option value="${currencyMethod.id}" data-method='${JSON.stringify(currencyMethod)}'>${currencyMethod.name}</option>`;
                    });

                    $(".empty-gateway").addClass('d-none');
                    $("form").removeClass('d-none');
                    $("#method-selection").removeClass('d-none');
                    $('select[name=method_code]').html(methodsOption);
                } else {
                    $(".empty-gateway").removeClass('d-none');
                    $("#method-selection").addClass('d-none');
                    $('.preview-details').addClass('d-none');
                }
            });

            $('select[name=method_code]').on('change', function() {
                if (!$(this).val()) {
                    $('.preview-details').addClass('d-none');
                    return false;
                }

                var resource = $('select[name=method_code] option:selected').data('method');
                var fixed_charge = parseFloat(resource.fixed_charge);
                var percent_charge = parseFloat(resource.percent_charge);

                $('.min').text(getAmount(resource.min_limit));
                $('.max').text(getAmount(resource.max_limit));

                var amount = parseFloat($('input[name=amount]').val());
                if (!amount) {
                    $('.preview-details').addClass('d-none');
                    return false;
                }

                $('.preview-details').removeClass('d-none');

                var charge = parseFloat(fixed_charge + (amount * percent_charge / 100));
                var payable = parseFloat((parseFloat(amount) - parseFloat(charge)));

                $('.charge').text(getAmount(charge));
                $('.payable').text(getAmount(payable));
            });

            $('input[name=amount]').on('input', function() {
                $('select[name=method_code]').change();
            });
            
            // Unbind dashboard canvas JS so it doesn't open the canvas when submitting
            $(document).off('submit', '.withdraw-form');
        })(jQuery);
    </script>
@endpush
