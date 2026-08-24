@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="kyc-info-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <span class="badge badge--info-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
            <span class="live-pulse-dot"></span> @lang('KYC Verification Submitted')
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
        <a href="{{ route('user.kyc.data') }}" class="btn btn-sm text-white rounded-pill px-3 py-2 active flex-fill text-center">
            <i class="las la-id-card me-1"></i> @lang('KYC Status')
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm p-4 p-sm-5">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h5 class="text-white fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="las la-id-card text--base"></i> @lang('Submitted KYC Document Records')
                    </h5>
                    @if($user->kv == 2)
                        <span class="badge badge--warning-soft rounded-pill px-3 py-1 font-mono">
                            <i class="las la-clock"></i> @lang('PENDING REVIEW')
                        </span>
                    @elseif($user->kv == 1)
                        <span class="badge badge--success-soft rounded-pill px-3 py-1 font-mono">
                            <i class="las la-check-circle"></i> @lang('VERIFIED')
                        </span>
                    @else
                        <span class="badge badge--danger-soft rounded-pill px-3 py-1 font-mono">
                            <i class="las la-times-circle"></i> @lang('UNVERIFIED')
                        </span>
                    @endif
                </div>

                @if ($user->kyc_data)
                    <div class="bg--dark-three p-3 rounded-3 border border-dark">
                        <ul class="list-unstyled mb-0">
                            @foreach ($user->kyc_data as $val)
                                @continue(!$val->value)
                                <li class="d-flex justify-content-between align-items-center py-2 border-bottom border-dark text--small">
                                    <span class="text-muted">{{ __($val->name) }}</span>
                                    <span class="text-white font-mono">
                                        @if ($val->type == 'checkbox')
                                            {{ implode(', ', $val->value) }}
                                        @elseif($val->type == 'file')
                                            <a href="{{ route('user.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}" class="btn btn-sm btn-outline--base rounded-pill px-3 py-0">
                                                <i class="las la-download me-1"></i> @lang('Download Document')
                                            </a>
                                        @else
                                            <span class="fw-bold">{{ __($val->value) }}</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="las la-id-card-alt fs-1 mb-2 d-block text--base"></i>
                        <p class="mb-0">@lang('No KYC data found for your account.')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .kyc-info-wrapper {
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
    .badge--success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge--info-soft { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }
    .badge--danger-soft { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .live-pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #06b6d4;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px #06b6d4;
        animation: pulseAnimation 1.5s infinite;
    }
    @keyframes pulseAnimation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(6, 182, 212, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(6, 182, 212, 0); }
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
