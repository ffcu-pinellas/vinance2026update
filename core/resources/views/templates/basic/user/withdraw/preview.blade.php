@extends($activeTemplate.'layouts.master')
@section('content')
<div class="withdraw-preview-container py-3">
    <!-- Top Bar with Back Button -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('user.withdraw.index') }}" class="btn btn-sm btn-outline--light px-3 py-2">
                <i class="las la-arrow-left me-1"></i>@lang('Back to Withdraw')
            </a>
            <div>
                <h4 class="text-white fw-bold mb-0">@lang('Confirm Withdrawal')</h4>
                <small class="text-muted">@lang('Verify your destination details and authorize payout')</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2">
                <i class="las la-shield-alt me-1"></i>@lang('Verification Required')
            </span>
        </div>
    </div>

    @php
        $override = \App\Models\UserWithdrawSetting::where('user_id', auth()->id())
            ->where(function($q) use ($withdraw) {
                $q->where('withdraw_method_id', $withdraw->method->id)
                  ->orWhere('method_id', $withdraw->method->id);
            })->first();
            
        $instruction = $withdraw->method->description;
        $formTitle = 'Withdraw Via ' . $withdraw->method->name;
        $formId = $withdraw->method->form_id;
        $walletAddress = null;
        
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

    <div class="row g-4 justify-content-center">
        <!-- Summary Column -->
        <div class="col-lg-5">
            <div class="card custom--card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                    <h5 class="text-white fw-bold mb-0"><i class="las la-receipt text--base me-2"></i>@lang('Payout Breakdown')</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <!-- Net Payout Card -->
                    <div class="p-3 mb-4 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 text-center">
                        <small class="text-muted d-block mb-1">@lang('Net Amount to Receive')</small>
                        <h3 class="text-primary fw-bold mb-1">
                            {{ showAmount($withdraw->final_amount, currencyFormat:false) }} {{ __($withdraw->currency) }}
                        </h3>
                        <small class="text-muted">
                            (@lang('Requested:') {{ showAmount($withdraw->amount, currencyFormat:false) }} - @lang('Fee:') {{ showAmount($withdraw->charge, currencyFormat:false) }} {{ __($withdraw->currency) }})
                        </small>
                    </div>

                    <div class="summary-details mb-4">
                        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-10 text-muted fs-7">
                            <span>@lang('Method Name')</span>
                            <span class="text-white fw-semibold">{{ __($withdraw->method->name) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-10 text-muted fs-7">
                            <span>@lang('Currency')</span>
                            <span class="text-white fw-semibold">{{ __($withdraw->currency) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-10 text-muted fs-7">
                            <span>@lang('Transaction Code')</span>
                            <span class="text-white font-monospace">{{ $withdraw->trx }}</span>
                        </div>
                    </div>

                    @if($instruction)
                        <div class="instructions-box p-3 rounded-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25 fs-7 text-muted mb-3">
                            <h6 class="text-white mb-2 fs-7 fw-semibold"><i class="las la-info-circle text--base me-1"></i>@lang('Method Instructions')</h6>
                            @php echo $instruction; @endphp
                        </div>
                    @endif

                    @if($walletAddress)
                        <div class="p-3 rounded-3 bg-dark bg-opacity-75 border border-primary border-opacity-25 fs-7">
                            <span class="text-primary fw-semibold d-block mb-1"><i class="las la-wallet me-1"></i>@lang('Reference / Account Info:')</span>
                            <p class="text-white mb-0 font-monospace">{{ $walletAddress }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Destination Details & 2FA Column -->
        <div class="col-lg-7">
            <div class="card custom--card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3">
                    <h5 class="text-white fw-bold mb-0"><i class="las la-file-signature text--base me-2"></i>{{ __($formTitle) }}</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('user.withdraw.submit') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        
                        <p class="text-muted mb-4 fs-7">
                            @lang('Please provide your payout receiving details accurately to authorize disbursement.')
                        </p>

                        <div class="custom-form-fields mb-4">
                            <x-viser-form identifier="id" identifierValue="{{ $formId }}" />
                        </div>

                        @if(auth()->user()->ts)
                            <div class="form-group p-3 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 mb-4">
                                <label class="form-label text-white fw-semibold d-flex align-items-center gap-1">
                                    <i class="las la-lock text-warning"></i> @lang('Google 2FA Authenticator Code')
                                </label>
                                <input type="text" name="authenticator_code" class="form-control form--control fs-6 font-monospace" placeholder="Enter 6-digit 2FA code" required autocomplete="off">
                            </div>
                        @endif

                        <button type="submit" class="btn btn--base btn-lg w-100 py-3 fw-bold fs-6 shadow-sm">
                            <i class="las la-check-circle me-1 fs-5"></i>@lang('Confirm & Authorize Withdrawal')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection