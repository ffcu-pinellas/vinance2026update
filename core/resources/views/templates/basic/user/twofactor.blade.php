@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="container">
        <!-- Security Hub Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mt-2">
            <h4 class="mb-0"><i class="fas fa-shield-alt text-primary me-2"></i>Security Hub</h4>
            
            <div class="table-header-menu">
                <a href="{{ route('user.profile.setting') }}" class="table-header-menu__link">Profile</a>
                <a href="{{ route('user.change.password') }}" class="table-header-menu__link">Password</a>
                <a href="{{ route('user.twofactor') }}" class="table-header-menu__link active">2FA Security</a>
            </div>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-12">
                <div class="card dashboard-card bg-primary-light border-primary-light">
                    <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between p-4">
                        <div class="d-flex align-items-center mb-3 mb-md-0">
                            <div class="vip-badge me-3 text-center" style="width: 50px; height: 50px; border-radius: 50%; background: var(--vn-accent); display: flex; align-items: center; justify-content: center; box-shadow: var(--vn-shadow-glow);">
                                <i class="fas fa-lock text-white fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 text-primary">Account Protection Score: @if(auth()->user()->ts) 100% @else 50% @endif</h4>
                                <p class="mb-0 text-muted fs-14">
                                    @if(auth()->user()->ts) 
                                        Your account is highly secure. Two-factor authentication is active.
                                    @else 
                                        Enable Two-Factor Authentication to reach 100% security and protect your funds.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center gy-4">
            @if (!auth()->user()->ts)
                <div class="col-md-6">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title">@lang('Add Your Account')</h5>
                        </div>

                        <div class="card-body">
                            <h6 class="mb-3">
                                @lang('Use the QR code or setup key on your Google Authenticator app to add your account.')
                            </h6>

                            <div class="form-group mx-auto text-center">
                                <img class="mx-auto" src="{{ $qrCodeUrl }}" alt="QR">
                            </div>

                            <div class="form-group">
                                <label class="form-label">@lang('Setup Key')</label>
                                <div class="input-group">
                                    <input type="text" name="key" value="{{ $secret }}" class="form-control form--control referralURL" readonly>
                                    <button type="button" class="input-group-text copytext" id="copyBoard"> <i class="fas fa-copy"></i> </button>
                                </div>
                            </div>

                            <label><i class="fas fa-info-circle"></i> @lang('Help')</label>
                            <p>@lang('Google Authenticator is a multifactor app for mobile devices. It generates timed codes used during the 2-step verification process. To use Google Authenticator, install the Google Authenticator application on your mobile device.') <a class="text--base" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank">@lang('Download')</a></p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-md-6">

                @if (auth()->user()->ts)
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title">@lang('Disable 2FA Security')</h5>
                        </div>
                        <form action="{{ route('user.twofactor.disable') }}" method="POST">
                            <div class="card-body">
                                @csrf
                                <input type="hidden" name="key" value="{{ $secret }}">
                                <div class="form-group">
                                    <label class="form-label">@lang('Google Authenticator OTP')</label>
                                    <input type="text" class="form-control form--control" name="code" required>
                                </div>
                                <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title">@lang('Enable 2FA Security')</h5>
                        </div>
                        <form action="{{ route('user.twofactor.enable') }}" method="POST">
                            <div class="card-body">
                                @csrf
                                <input type="hidden" name="key" value="{{ $secret }}">
                                <div class="form-group">
                                    <label class="form-label">@lang('Google Authenticator OTP')</label>
                                    <input type="text" class="form-control form--control" name="code" required>
                                </div>
                                <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .copied::after {
            background-color: #{{ gs('base_color') }};
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('#copyBoard').on('click', function() {
                var copyText = document.getElementsByClassName("referralURL");
                copyText = copyText[0];
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                /*For mobile devices*/
                document.execCommand("copy");
                copyText.blur();
                this.classList.add('copied');
                setTimeout(() => this.classList.remove('copied'), 1500);
            });
        })(jQuery);
    </script>
@endpush
