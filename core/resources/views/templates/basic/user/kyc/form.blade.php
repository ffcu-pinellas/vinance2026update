@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="kyc-form-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <span class="badge badge--warning-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
            <span class="live-pulse-dot"></span> @lang('KYC Verification Required')
        </span>
    </div>

    <!-- Shared Account Center Navigation Pill Bar -->
    <div class="account-nav-bar p-1 rounded-pill bg--dark-two d-flex flex-wrap gap-1 shadow-sm mb-4">
        <a href="{{ route('user.profile.setting') }}" class="btn btn-sm text-muted rounded-pill px-3 py-2 flex-fill text-center">
            <i class="las la-user-circle me-1"></i> @lang('Profile Information')
        </a>
        <a href="{{ route('user.twofactor') }}" class="btn btn-sm text-muted rounded-pill px-3 py-2 flex-fill text-center">
            <i class="las la-shield-alt me-1"></i> @lang('2FA Security')
        </a>
        <a href="{{ route('user.change.password') }}" class="btn btn-sm text-muted rounded-pill px-3 py-2 flex-fill text-center">
            <i class="las la-key me-1"></i> @lang('Change Password')
        </a>
        <a href="{{ route('user.kyc.form') }}" class="btn btn-sm text-white rounded-pill px-3 py-2 active flex-fill text-center">
            <i class="las la-id-card me-1"></i> @lang('KYC Verification')
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm p-4 p-sm-5">
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="text-white fw-bold mb-1 d-flex align-items-center gap-2">
                            <i class="las la-id-card text--base"></i> @lang('Identity Verification (KYC)')
                        </h5>
                        <p class="text-muted text--small mb-0">@lang('Submit your identity documents to lift withdrawal limits and unlock VIP institutional privileges.')</p>
                    </div>
                    <span class="badge badge--primary-soft rounded-pill px-3 py-1 font-mono">
                        <i class="las la-shield-alt"></i> @lang('Level 2 VIP Verification')
                    </span>
                </div>

                <form action="{{ route('user.kyc.submit') }}" method="post" enctype="multipart/form-data" class="mt-3">
                    @csrf

                    <x-viser-form identifier="act" identifierValue="kyc" />

                    <button type="submit" class="btn btn--base w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm mt-4">
                        <i class="las la-paper-plane me-1"></i> @lang('Submit KYC Verification Data')
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .kyc-form-wrapper {
        color: #e2e8f0;
        max-width: 100%;
        overflow-x: clip;
    }
    .font-mono {
        font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', Courier, monospace !important;
    }
    .bg--dark-two {
        background: #0f172a !important;
    }
    .bg--dark-three {
        background: #1e293b !important;
    }
    .account-nav-bar {
        border: 1px solid #334155;
    }
    .account-nav-bar .btn.active {
        background: #3b82f6 !important;
        color: #fff !important;
        font-weight: 600;
    }
    .badge--warning-soft { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge--primary-soft { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .live-pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #f59e0b;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px #f59e0b;
        animation: pulseAnimation 1.5s infinite;
    }
    @keyframes pulseAnimation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }

    /* Hide the topbar/sidebar hamburger menu button on mobile so back button is exclusively used */
    @media (max-width: 1199px) {
        .dashboard-body__bar,
        .dashboard-sidebar-filter__button,
        .dashboardBodyNav {
            display: none !important;
        }
    }
</style>
@endpush
