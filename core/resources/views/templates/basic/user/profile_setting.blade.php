@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="profile-settings-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <span class="badge badge--success-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
            <span class="live-pulse-dot"></span> @lang('Account Protected')
        </span>
    </div>

    <!-- Shared Account Center Navigation Pill Bar -->
    <div class="account-nav-bar p-1 rounded-pill bg--dark-two d-flex flex-wrap gap-1 shadow-sm mb-4">
        <a href="{{ route('user.profile.setting') }}" class="btn btn-sm text-white rounded-pill px-3 py-2 active flex-fill text-center">
            <i class="las la-user-circle me-1"></i> @lang('Profile Information')
        </a>
        <a href="{{ route('user.twofactor') }}" class="btn btn-sm text-muted rounded-pill px-3 py-2 flex-fill text-center">
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
        <!-- User ID & Identity Overview Card -->
        <div class="col-lg-4">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm p-4 text-center h-100">
                <div class="profile-avatar-box mx-auto mb-3 position-relative">
                    <img src="{{ getImage(getFilePath('userProfile').'/'. $user->image, getFileSize('userProfile'), true) }}" alt="Avatar" class="rounded-circle profile-avatar-img shadow" id="avatarPreview">
                    <span class="badge badge--success rounded-circle position-absolute bottom-0 end-0 p-2 border border-2 border-dark" title="Active"></span>
                </div>

                <h4 class="text-white fw-bold mb-1">{{ $user->fullname }}</h4>
                <p class="text-muted text--small font-mono mb-2">@<span>{{ $user->username }}</span></p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge badge--primary-soft rounded-pill px-3 py-1 font-mono text--small">UID: #{{ $user->id }}</span>
                    <span class="badge badge--success-soft rounded-pill px-3 py-1 text--small">
                        <i class="las la-check-circle"></i> @lang('Verified')
                    </span>
                </div>

                <div class="bg--dark-three p-3 rounded-3 border border-dark text-start text--small mb-3">
                    <div class="d-flex justify-content-between py-1 border-bottom border-dark">
                        <span class="text-muted">@lang('Email'):</span>
                        <strong class="text-white font-mono">{{ $user->email }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom border-dark">
                        <span class="text-muted">@lang('Mobile'):</span>
                        <strong class="text-white font-mono">+{{ $user->mobile }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">@lang('Member Since'):</span>
                        <strong class="text-white font-mono">{{ $user->created_at->format('M Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form Card -->
        <div class="col-lg-8">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm p-4 p-sm-5">
                <h5 class="text-white fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="las la-user-edit text--base"></i> @lang('Personal Profile Details')
                </h5>
                <p class="text-muted text--small mb-4">@lang('Update your personal identity details and address information')</p>

                <form action="" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label text-muted text--small text-uppercase">@lang('First Name')</label>
                            <input type="text" class="form-control bg--dark-three text-white border-dark" name="firstname" value="{{ $user->firstname }}" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label text-muted text--small text-uppercase">@lang('Last Name')</label>
                            <input type="text" class="form-control bg--dark-three text-white border-dark" name="lastname" value="{{ $user->lastname }}" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label text-muted text--small text-uppercase">@lang('State / Province')</label>
                            <input type="text" class="form-control bg--dark-three text-white border-dark" name="state" value="{{ @$user->state }}">
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label text-muted text--small text-uppercase">@lang('City')</label>
                            <input type="text" class="form-control bg--dark-three text-white border-dark" name="city" value="{{ @$user->city }}">
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label text-muted text--small text-uppercase">@lang('Zip / Postal Code')</label>
                            <input type="text" class="form-control bg--dark-three text-white border-dark font-mono" name="zip" value="{{ @$user->zip }}">
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label text-muted text--small text-uppercase">@lang('Address')</label>
                            <input type="text" class="form-control bg--dark-three text-white border-dark" name="address" value="{{ @$user->address }}">
                        </div>

                        <div class="col-12 form-group">
                            <label class="form-label text-muted text--small text-uppercase">@lang('Profile Avatar Image')</label>
                            <input type="file" class="form-control bg--dark-three text-white border-dark" name="image" id="avatarFileInput" accept=".png, .jpg, .jpeg">
                            <small class="text-muted">@lang('Supported formats: PNG, JPG, JPEG. Max size 2MB.')</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn--base w-100 rounded-pill py-3 fw-bold fs-6 shadow-sm mt-4">
                        <i class="las la-save me-1"></i> @lang('Save Profile Changes')
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .profile-settings-wrapper {
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
    .profile-avatar-box {
        width: 110px;
        height: 110px;
    }
    .profile-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 3px solid #3b82f6;
    }
    .badge--success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge--primary-soft { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
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

        // Preview avatar image before upload
        $('#avatarFileInput').on('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#avatarPreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    })(jQuery);
</script>
@endpush
