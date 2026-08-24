@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="manual-deposit-container py-3">
    <!-- Top Bar with Back Button -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('user.deposit.index') }}" class="btn btn-sm btn-outline--light px-3 py-2">
                <i class="las la-arrow-left me-1"></i>@lang('Back to Deposit')
            </a>
            <div>
                <h4 class="text-white fw-bold mb-0">{{ __($pageTitle) }}</h4>
                <small class="text-muted">@lang('Follow the instructions below to complete your payment')</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2">
                <i class="las la-clock me-1"></i>@lang('Awaiting Payment')
            </span>
        </div>
    </div>

    @php
        $gatewayCurrency = $method ?? $data->gatewayCurrency();
        $override = \App\Models\UserDepositSetting::where('user_id', auth()->id())->where('gateway_currency_id', $gatewayCurrency->id)->first();
        $instruction = $gateway->description;
        $walletAddress = $gateway->wallet_address;
        $formTitle = 'Deposit Instructions';
        $formId = $gateway->form_id;
        
        if ($override) {
            if ($override->form_id) {
                $formId = $override->form_id;
            }
            if ($override->payment_info) {
                $instruction = $override->payment_info;
            }
            if ($override->wallet_address) {
                $walletAddress = $override->wallet_address;
            }
            if ($override->form_title) {
                $formTitle = $override->form_title;
            }
        }
    @endphp

    <div class="row g-4">
        <!-- Payment Details & QR Column -->
        <div class="col-lg-6">
            <div class="card custom--card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                    <h5 class="text-white fw-bold mb-0"><i class="las la-info-circle text--base me-2"></i>{{ __($formTitle) }}</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <!-- Amount Bill Card -->
                    <div class="p-3 mb-4 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-center">
                        <small class="text-muted d-block mb-1">@lang('Amount to Send')</small>
                        <h3 class="text-success fw-bold mb-1">
                            {{ showAmount($data['final_amount'], currencyFormat:false) }} {{ __($data->method_currency) }}
                        </h3>
                        <small class="text-muted">
                            (@lang('Requested:') {{ showAmount($data['amount'], currencyFormat:false) }} + @lang('Fee:') {{ showAmount($data['charge'], currencyFormat:false) }} {{ __($data->method_currency) }})
                        </small>
                    </div>

                    <!-- Custom Payment Instructions -->
                    <div class="payment-instructions text-muted mb-4 fs-7">
                        @php echo $instruction @endphp
                    </div>

                    <!-- QR Code & Copyable Wallet -->
                    @if($walletAddress)
                        <div class="qr-container text-center p-4 rounded-3 bg-dark bg-opacity-75 border border-secondary border-opacity-50 my-4">
                            <h6 class="text-white fw-semibold mb-3"><i class="las la-qrcode text-success me-1"></i>@lang('Scan QR or Copy Address')</h6>
                            <div class="qr-image-box d-inline-block p-2 bg-white rounded-3 shadow mb-3">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ $walletAddress }}" alt="QR Code" style="width: 160px; height: 160px; display: block;">
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control text-center bg-dark text-white border-secondary fs-7" value="{{ $walletAddress }}" readonly id="walletAddressInput">
                                <button class="btn btn--base px-3" type="button" onclick="copyWalletAddress()">
                                    <i class="las la-copy me-1"></i>@lang('Copy')
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Verification & Custom Form Column -->
        <div class="col-lg-6">
            <div class="card custom--card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                    <h5 class="text-white fw-bold mb-0"><i class="las la-file-invoice text--base me-2"></i>@lang('Payment Proof & Verification')</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('user.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <p class="text-muted mb-4 fs-7">
                            @lang('Please submit the required transaction details below after making the transfer to expedite processing.')
                        </p>

                        <div class="custom-form-fields mb-4">
                            <x-viser-form identifier="id" identifierValue="{{ $formId }}" />
                        </div>

                        <button type="submit" class="btn btn--base btn-lg w-100 py-3 fw-bold fs-6 mt-3 shadow-sm">
                            <i class="las la-check-circle me-1 fs-5"></i>@lang('Confirm & Complete Payment')
                        </button>
                    </form>

                    <div class="mt-4 p-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-25">
                        <small class="text-muted d-block">
                            <i class="las la-shield-alt text-success me-1"></i>@lang('Your deposit request will be verified and credited promptly after network confirmation.')
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    function copyWalletAddress() {
        var copyText = document.getElementById("walletAddressInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(copyText.value);
        } else {
            document.execCommand("copy");
        }
        notify('success', 'Wallet address copied to clipboard!');
    }
</script>
@endpush
