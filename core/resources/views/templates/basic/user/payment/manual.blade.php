@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card custom--card">
                <div class="card-header card-header-bg">
                    <h5 class="card-title">{{ __($pageTitle) }}</h5>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('user.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <p class="text-center mt-2">
                                    @lang('You have requested') <b class="text--success">
                                        {{ showAmount($data['amount'],currencyFormat:false) }}
                                        {{ __(@$data->method_currency) }}</b> , @lang('Please pay')
                                    <b class="text--success">
                                        {{ showAmount($data['amount'],currencyFormat:false) }} +
                                        <span data-bs-toggle="tooltip" title="@lang('Charge')">{{ showAmount($data['charge'],currencyFormat:false) }}</span> =
                                        {{ showAmount($data['final_amount'],currencyFormat:false) . ' ' . $data['method_currency'] }}
                                    </b> @lang('for successful payment')
                                </p>
                                <h4 class="mb-4">@lang('Please follow the instruction below')</h4>
                                @php
                                    $override = \App\Models\UserDepositSetting::where('user_id', auth()->id())->where('gateway_currency_id', $method->id)->first();
                                    $instruction = $data->gateway->description;
                                    $walletAddress = $data->gateway->wallet_address;
                                    
                                    if ($override) {
                                        if ($override->form_id) {
                                            $gateway->form_id = $override->form_id;
                                        }
                                        if ($override->payment_info) {
                                            $instruction = $override->payment_info;
                                        }
                                        if ($override->wallet_address) {
                                            $walletAddress = $override->wallet_address;
                                        }
                                    }
                                @endphp

                                <p class="my-4">@php echo $instruction @endphp</p>
                                
                                @if($walletAddress)
                                    <div class="text-center my-4">
                                        <h5 class="mb-3">@lang('Deposit Address')</h5>
                                        <div class="d-flex justify-content-center mb-3">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $walletAddress }}" alt="QR Code">
                                        </div>
                                        <div class="input-group justify-content-center">
                                            <input type="text" class="form-control" style="max-width: 300px;" value="{{ $walletAddress }}" readonly id="walletAddress">
                                            <button class="btn btn--base input-group-text" type="button" onclick="copyText('walletAddress')">@lang('Copy')</button>
                                        </div>
                                    </div>
                                    @push('script')
                                    <script>
                                        function copyText(id) {
                                            var copyText = document.getElementById(id);
                                            copyText.select();
                                            copyText.setSelectionRange(0, 99999);
                                            document.execCommand("copy");
                                            notify('success', 'Copied: ' + copyText.value);
                                        }
                                    </script>
                                    @endpush
                                @endif
                                
                            </div>
                            <x-viser-form identifier="id" identifierValue="{{ $gateway->form_id }}" />
                            <div class="col-md-12">
                                <button type="submit" class="btn btn--base w-100">@lang('Pay Now')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
