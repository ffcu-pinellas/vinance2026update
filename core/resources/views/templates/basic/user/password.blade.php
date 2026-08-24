@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="password-settings-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <span class="badge badge--success-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
            <span class="live-pulse-dot"></span> @lang('Credential Protection Active')
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
        <a href="{{ route('user.change.password') }}" class="btn btn-sm text-white rounded-pill px-3 py-2 active flex-fill text-center">
            <i class="las la-key me-1"></i> @lang('Change Password')
        </a>
        <a href="{{ route('user.kyc.form') }}" class="btn btn-sm text-muted rounded-pill px-3 py-2 flex-fill text-center">
            <i class="las la-id-card me-1"></i> @lang('KYC Verification')
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm p-4 p-sm-5">
                <h5 class="text-white fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="las la-key text--base"></i> @lang('Update Account Password')
                </h5>
                <p class="text-muted text--small mb-4">@lang('Ensure your account is using a long, random password to stay secure.')</p>

                <form action="" method="post">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="form-label text-muted text--small text-uppercase">@lang('Current Password')</label>
                        <input type="password" class="form-control bg--dark-three text-white border-dark font-mono" name="current_password" required autocomplete="current-password" placeholder="••••••••">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label text-muted text--small text-uppercase">@lang('New Password')</label>
                        <input type="password" class="form-control bg--dark-three text-white border-dark font-mono @if(gs('secure_password')) secure-password @endif" name="password" required autocomplete="new-password" placeholder="••••••••">
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label text-muted text--small text-uppercase">@lang('Confirm New Password')</label>
                        <input type="password" class="form-control bg--dark-three text-white border-dark font-mono" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn--base w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm">
                        <i class="las la-lock me-1"></i> @lang('Save New Password')
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@if(gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('style')
<style>
    .password-settings-wrapper {
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
    .badge--success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .live-pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px #10b981;
        animation: pulseAnimation 1.5s infinite;
    }
    @keyframes pulseAnimation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
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
