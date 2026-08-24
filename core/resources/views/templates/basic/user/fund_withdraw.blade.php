@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card custom--card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">@lang('Fund / Withdraw')</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4 text-center">@lang('Please select the action you would like to perform.')</p>
                <div class="row justify-content-center gy-4">
                    <div class="col-md-6 col-lg-5">
                        <div class="card bg--dark shadow-sm border border-primary h-100 selection-card" onclick="window.location.href='{{ route('user.deposit.index') }}'" style="cursor:pointer; transition: transform 0.2s;">
                            <div class="card-body text-center p-5">
                                <div class="icon-circle bg-primary text-white mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                    <i class="las la-wallet"></i>
                                </div>
                                <h4 class="mb-2 text-white">@lang('Deposit Funds')</h4>
                                <p class="text-white-50">@lang('Add money to your Vinance wallet via crypto or manual gateways.')</p>
                                <button class="btn btn--primary w-100 mt-3">@lang('Proceed to Deposit')</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-5">
                        <div class="card bg--dark shadow-sm border border-danger h-100 selection-card" onclick="window.location.href='{{ route('user.withdraw') }}'" style="cursor:pointer; transition: transform 0.2s;">
                            <div class="card-body text-center p-5">
                                <div class="icon-circle bg-danger text-white mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                    <i class="las la-money-bill-wave"></i>
                                </div>
                                <h4 class="mb-2 text-white">@lang('Withdraw Funds')</h4>
                                <p class="text-white-50">@lang('Request a withdrawal of your earnings or available balance.')</p>
                                <button class="btn btn--danger w-100 mt-3">@lang('Proceed to Withdraw')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    .selection-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
    }
</style>
@endpush
@endsection
