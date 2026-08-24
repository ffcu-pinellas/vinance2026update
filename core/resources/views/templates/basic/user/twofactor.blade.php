@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="twofactor-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <span class="badge badge--success-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
            <span class="live-pulse-dot"></span> @lang('Security Engine Online')
        </span>
    </div>

    <!-- Shared Account Center Navigation Pill Bar -->
    <div class="account-nav-bar p-1 rounded-pill bg--dark-two d-flex flex-wrap gap-1 shadow-sm mb-4">
        <a href="{{ route('user.profile.setting') }}" class="btn btn-sm text-muted rounded-pill px-3 py-2 flex-fill text-center">
            <i class="las la-user-circle me-1"></i> @lang('Profile Information')
        </a>
        <a href="{{ route('user.twofactor') }}" class="btn btn-sm text-white rounded-pill px-3 py-2 active flex-fill text-center">
            <i class="las la-shield-alt me-1"></i> @lang('2FA Security')
        </a>
        <a href="{{ route('user.change.password') }}" class="btn btn-sm text-muted rounded-pill px-3 py-2 flex-fill text-center">
            <i class="las la-key me-1"></i> @lang('Change Password')
        </a>
        <a href="{{ route('user.kyc.form') }}" class="btn btn-sm text-muted rounded-pill px-3 py-2 flex-fill text-center">
            <i class="las la-id-card me-1"></i> @lang('KYC Verification')
        </a>
    </div>

    <div class="row g-4">
        @if (!auth()->user()->ts)
            <!-- Enable 2FA: QR Code Card -->
            <div class="col-lg-6">
                <div class="card bg--dark-two border-0 rounded-4 shadow-sm p-4 p-sm-5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="text-white fw-bold mb-1 d-flex align-items-center gap-2">
                            <i class="las la-qrcode text--base"></i> @lang('Scan Google Authenticator QR')
                        </h5>
                        <p class="text-muted text--small mb-3">@lang('Open Google Authenticator app on your phone and scan the QR code below.')</p>

                        <div class="qr-code-box bg-white p-3 rounded-4 mx-auto my-3 text-center" style="max-width: 180px;">
                            <img src="{{ $qrCodeUrl }}" alt="2FA QR Code" class="img-fluid">
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label text-muted text--small text-uppercase">@lang('Or Enter Setup Key Manually')</label>
                            <div class="input-group">
                                <input type="text" name="key" value="{{ $secret }}" class="form-control bg--dark-three text-white border-dark font-mono text--small" id="secretKeyInput" readonly>
                                <button type="button" class="btn btn--base px-3" id="copySecretBtn">
                                    <i class="las la-copy me-1"></i> <span id="copySecretText">@lang('Copy')</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg--dark-three rounded-3 border border-dark text-muted text--small">
                        <i class="las la-info-circle text--info me-1"></i>
                        @lang('Google Authenticator generates time-sensitive 6-digit OTP codes for account login & withdrawal protection.')
                    </div>
                </div>
            </div>

            <!-- Enable 2FA: Verification Input Card -->
            <div class="col-lg-6">
                <div class="card bg--dark-two border-0 rounded-4 shadow-sm p-4 p-sm-5 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="text-white fw-bold mb-1 d-flex align-items-center gap-2">
                            <i class="las la-lock text--base"></i> @lang('Activate 2FA Protection')
                        </h5>
                        <p class="text-muted text--small mb-4">@lang('Enter the 6-digit code displayed in your Authenticator app to confirm activation.')</p>

                        <form action="{{ route('user.twofactor.enable') }}" method="POST">
                            @csrf
                            <input type="hidden" name="key" value="{{ $secret }}">

                            <div class="form-group mb-4">
                                <label class="form-label text-muted text--small text-uppercase">@lang('6-Digit Authenticator OTP')</label>
                                <input type="text" name="code" class="form-control bg--dark-three text-white border-dark font-mono fs-4 text-center letter-spacing-2" placeholder="000000" maxlength="6" required autocomplete="off">
                            </div>

                            <button type="submit" class="btn btn--base w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm">
                                <i class="las la-shield-alt me-1"></i> @lang('Verify & Enable 2FA')
                            </button>
                        </form>
                    </div>

                    <div class="mt-4 p-3 bg--dark-three rounded-3 border border-dark text-muted text--small">
                        <i class="las la-mobile text--warning me-1"></i>
                        @lang('Do not delete your Authenticator account. Keep your setup key stored safely as backup.')
                    </div>
                </div>
            </div>
        @else
            <!-- 2FA Active: Disable Option -->
            <div class="col-lg-8 mx-auto">
                <div class="card bg--dark-two border-0 rounded-4 shadow-sm p-4 p-sm-5 text-center">
                    <div class="twofa-active-icon mx-auto mb-3">
                        <i class="las la-shield-alt"></i>
                    </div>

                    <h4 class="text-white fw-bold mb-1">@lang('Two-Factor Authentication is Active')</h4>
                    <p class="text-muted text--small mb-4 mx-auto" style="max-width: 440px;">
                        @lang('Your account is safeguarded with Google Authenticator OTP verification on sensitive operations.')
                    </p>

                    <form action="{{ route('user.twofactor.disable') }}" method="POST" class="text-start mx-auto w-100" style="max-width: 460px;">
                        @csrf
                        <input type="hidden" name="key" value="{{ $secret }}">

                        <div class="form-group mb-4">
                            <label class="form-label text-muted text--small text-uppercase">@lang('Enter Authenticator OTP to Disable')</label>
                            <input type="text" name="code" class="form-control bg--dark-three text-white border-dark font-mono fs-4 text-center" placeholder="000000" maxlength="6" required autocomplete="off">
                        </div>

                        <button type="submit" class="btn btn-outline--danger w-100 rounded-pill py-3 fw-bold fs-6">
                            <i class="las la-ban me-1"></i> @lang('Disable 2FA Security')
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('style')
<style>
    .twofactor-wrapper {
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
    .twofa-active-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
    }
    .letter-spacing-2 {
        letter-spacing: 4px;
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

@push('script')
<script>
    (function ($) {
        "use strict";

        $('#copySecretBtn').on('click', function () {
            var copyText = document.getElementById("secretKeyInput");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            $('#copySecretText').text("@lang('Copied!')");
            notify('success', '@lang("2FA secret key copied to clipboard!")');
            setTimeout(function() {
                $('#copySecretText').text("@lang('Copy')");
            }, 2000);
        });
    })(jQuery);
</script>
@endpush
