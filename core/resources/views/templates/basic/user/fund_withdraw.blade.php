@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="fund-withdraw-container py-4">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="text-white fw-bold mb-1"><i class="las la-exchange-alt text--base me-2"></i>@lang('Fund / Withdraw Hub')</h4>
            <p class="text-muted mb-0">@lang('Manage your capital flows, deposit crypto & fiat, or request fast withdrawals.')</p>
        </div>
        <div>
            <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm px-3">
                <i class="las la-arrow-left me-1"></i>@lang('Back to Dashboard')
            </a>
        </div>
    </div>

    <!-- Hub Cards -->
    <div class="row g-4 justify-content-center">
        <!-- Deposit Card -->
        <div class="col-lg-6 col-md-6">
            <div class="action-card deposit-card h-100 p-4 p-xl-5">
                <div class="action-card__badge">
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2">
                        <i class="las la-bolt me-1"></i>@lang('Instant Processing')
                    </span>
                </div>
                <div class="action-card__icon mb-4">
                    <div class="icon-box icon-box--success">
                        <i class="las la-wallet"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-2">@lang('Deposit Funds')</h3>
                <p class="text-muted mb-4">@lang('Add funds to your spot or funding wallet via cryptocurrency networks or fiat gateways.')</p>
                
                <ul class="feature-list mb-5">
                    <li><i class="las la-check-circle text-success me-2"></i>@lang('Automated QR Code generation & direct wallet addressing')</li>
                    <li><i class="las la-check-circle text-success me-2"></i>@lang('Support for BTC, ETH, USDT (TRC20/ERC20/BEP20), and Fiat')</li>
                    <li><i class="las la-check-circle text-success me-2"></i>@lang('Instant balance crediting upon network confirmation')</li>
                </ul>

                <a href="{{ route('user.deposit.index') }}" class="btn btn--base w-100 py-3 fw-bold fs-6">
                    <i class="las la-arrow-circle-right me-2 fs-5"></i>@lang('Proceed to Deposit')
                </a>
            </div>
        </div>

        <!-- Withdraw Card -->
        <div class="col-lg-6 col-md-6">
            <div class="action-card withdraw-card h-100 p-4 p-xl-5">
                <div class="action-card__badge">
                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2">
                        <i class="las la-shield-alt me-1"></i>@lang('2FA Protected Payouts')
                    </span>
                </div>
                <div class="action-card__icon mb-4">
                    <div class="icon-box icon-box--primary">
                        <i class="las la-money-bill-wave"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-2">@lang('Withdraw Funds')</h3>
                <p class="text-muted mb-4">@lang('Request a fast withdrawal of your trading earnings or available balance to external destinations.')</p>
                
                <ul class="feature-list mb-5">
                    <li><i class="las la-check-circle text-primary me-2"></i>@lang('Low gas fees and high-speed settlement processing')</li>
                    <li><i class="las la-check-circle text-primary me-2"></i>@lang('Custom destination accounts, bank routing & crypto wallets')</li>
                    <li><i class="las la-check-circle text-primary me-2"></i>@lang('Real-time notification tracking on payout progress')</li>
                </ul>

                <a href="{{ route('user.withdraw.index') }}" class="btn btn-outline--light w-100 py-3 fw-bold fs-6 hover-withdraw">
                    <i class="las la-arrow-circle-up me-2 fs-5"></i>@lang('Proceed to Withdraw')
                </a>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
.action-card {
    background: #141923;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}
.action-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
    transition: all 0.3s ease;
}
.deposit-card:hover {
    border-color: rgba(0, 192, 135, 0.4);
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 192, 135, 0.12);
}
.deposit-card:hover::before {
    background: #00C087;
}
.withdraw-card:hover {
    border-color: rgba(56, 97, 251, 0.4);
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(56, 97, 251, 0.12);
}
.withdraw-card:hover::before {
    background: #3861FB;
}
.icon-box {
    width: 68px;
    height: 68px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}
.icon-box--success {
    background: rgba(0, 192, 135, 0.12);
    color: #00C087;
    border: 1px solid rgba(0, 192, 135, 0.25);
}
.icon-box--primary {
    background: rgba(56, 97, 251, 0.12);
    color: #3861FB;
    border: 1px solid rgba(56, 97, 251, 0.25);
}
.feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
    flex-grow: 1;
}
.feature-list li {
    color: #8B94A5;
    font-size: 14px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
}
.hover-withdraw:hover {
    background: #3861FB !important;
    border-color: #3861FB !important;
    color: #fff !important;
}
</style>
@endpush
@endsection
