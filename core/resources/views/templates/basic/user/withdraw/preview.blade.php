@extends($activeTemplate.'layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card custom--card">
            @php
                $override = \App\Models\UserWithdrawSetting::where('user_id', auth()->id())->where('withdraw_method_id', $withdraw->method->id)->first();
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
            <div class="card-header">
                <h5 class="card-title">{{ __($formTitle) }}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('user.withdraw.submit')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        @php echo $instruction; @endphp
                    </div>
                    @if($walletAddress)
                        <div class="alert alert-info my-3">
                            <h6 class="mb-1">@lang('Specific Account/Wallet Info:')</h6>
                            <p class="mb-0">{{ $walletAddress }}</p>
                        </div>
                    @endif
                    <x-viser-form identifier="id" identifierValue="{{ $formId }}" />
                    @if(auth()->user()->ts)
                    <div class="form-group">
                        <label>@lang('Google Authenticator Code')</label>
                        <input type="text" name="authenticator_code" class="form-control form--control" required>
                    </div>
                    @endif
                    <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection