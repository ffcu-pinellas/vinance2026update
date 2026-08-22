@php
    $user = auth()->user();
@endphp
<div class="dashboard-header">
    <div class="dashboard-header__inner">
        <!-- Left Side: Mobile Menu Button & Breadcrumb -->
        <div class="dashboard-header__left d-flex align-items-center gap-3">
            <button class="dashboard-sidebar-filter__button d-xl-none btn btn--dark btn--sm p-2" type="button" aria-label="Toggle Sidebar">
                <i class="las la-bars fs-18"></i>
            </button>
            <div class="dashboard-breadcrumb d-none d-md-block">
                <h5 class="mb-0 fs-16 fw-600 text-white">{{ __($pageTitle ?? 'Dashboard') }}</h5>
            </div>
        </div>

        <!-- Right Side: Fast Trading & Account Info -->
        <div class="dashboard-header__right d-flex align-items-center gap-2 gap-md-3">
            <!-- Compact Referral Link Box -->
            <div class="copy-link d-none d-lg-flex align-items-center">
                <span class="fs-12 text--muted ps-2"><i class="las la-link"></i> Ref:</span>
                <input type="text" class="copyText fs-12 px-2" value="{{ route('home') }}?reference={{ $user->username }}" readonly style="width: 140px;">
                <button class="copy-link__button copyTextBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Copy Referral URL')">
                    <span class="copy-link__icon"><i class="las la-copy"></i></span>
                </button>
            </div>

            <!-- Direct Fast Trading Links -->
            <a href="{{ route('trade') }}" target="_blank" class="btn btn--dark btn--sm trade-btn d-none d-sm-inline-flex align-items-center gap-1">
                <i class="las la-chart-bar text--base"></i>
                <span>@lang('Spot Trade')</span>
            </a>
            <a href="{{ route('binary') }}" target="_blank" class="btn btn--base btn--sm trade-btn d-none d-sm-inline-flex align-items-center gap-1">
                <i class="las la-bolt"></i>
                <span>@lang('Binary Pro')</span>
            </a>

            <!-- User Profile Dropdown Pill -->
            <div class="user-info">
                <div class="user-info__button">
                    <div class="user-avatar-pill d-flex align-items-center gap-2">
                        <div class="avatar-circle">
                            {{ strtoupper(substr($user->username, 0, 2)) }}
                        </div>
                        <div class="user-info__profile d-none d-md-block text-start">
                            <p class="user-info__name mb-0 fs-13 fw-600 text-white">{{ __($user->username) }}</p>
                            <span class="fs-11 @if($user->kv == Status::KYC_VERIFIED) text--success @else text--warning @endif">
                                @if($user->kv == Status::KYC_VERIFIED)
                                    <i class="las la-check-circle"></i> @lang('Verified')
                                @else
                                    <i class="las la-shield-alt"></i> @lang('Unverified')
                                @endif
                            </span>
                        </div>
                        <i class="las la-angle-down fs-12 text--muted ms-1"></i>
                    </div>
                </div>

                <ul class="user-info-dropdown">
                    <li class="user-info-dropdown__header p-3 border-bottom border-secondary">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle avatar-circle-lg">
                                {{ strtoupper(substr($user->username, 0, 2)) }}
                            </div>
                            <div>
                                <h6 class="mb-0 fs-14 text-white">{{ __($user->fullname ?? $user->username) }}</h6>
                                <small class="text--muted fs-12">{{ __($user->email) }}</small>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 d-flex justify-content-between align-items-center border-top border-dark fs-12">
                            <span class="text--muted">UID: {{ $user->id + 100000 }}</span>
                            <span class="badge @if($user->kv == Status::KYC_VERIFIED) badge--success @else badge--warning @endif">
                                {{ $user->kv == Status::KYC_VERIFIED ? __('KYC Verified') : __('KYC Required') }}
                            </span>
                        </div>
                    </li>
                    <li class="user-info-dropdown__item">
                        <a class="user-info-dropdown__link" href="{{ route('user.profile.setting') }}">
                            <span class="icon"><i class="far fa-user-circle"></i></span>
                            <span class="text">@lang('My Profile')</span>
                        </a>
                    </li>
                    <li class="user-info-dropdown__item">
                        <a class="user-info-dropdown__link" href="{{ route('user.kyc.form') }}">
                            <span class="icon"><i class="las la-id-card"></i></span>
                            <span class="text">@lang('Identity Verification')</span>
                        </a>
                    </li>
                    <li class="user-info-dropdown__item">
                        <a class="user-info-dropdown__link" href="{{ route('user.twofactor') }}">
                            <span class="icon"><i class="las la-shield-alt"></i></span>
                            <span class="text">@lang('Security & 2FA')</span>
                        </a>
                    </li>
                    <li class="user-info-dropdown__item">
                        <a class="user-info-dropdown__link" href="{{ route('user.change.password') }}">
                            <span class="icon"><i class="las la-key"></i></span>
                            <span class="text">@lang('Change Password')</span>
                        </a>
                    </li>
                    <li class="user-info-dropdown__item border-top border-secondary mt-1 pt-1">
                        <a class="user-info-dropdown__link text--danger" href="{{ route('user.logout') }}">
                            <span class="icon text--danger"><i class="las la-sign-out-alt"></i></span>
                            <span class="text">@lang('Logout')</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
