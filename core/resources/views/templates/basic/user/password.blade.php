@extends($activeTemplate.'layouts.master')
@section('content')
<div class="container-fluid px-4">
    <!-- Security Hub Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mt-2">
        <h4 class="mb-0"><i class="fas fa-shield-alt text-primary me-2"></i>Security Hub</h4>
        
        <div class="table-header-menu">
            <a href="{{ route('user.profile.setting') }}" class="table-header-menu__link">Profile</a>
            <a href="{{ route('user.change.password') }}" class="table-header-menu__link active">Password</a>
            <a href="{{ route('user.twofactor') }}" class="table-header-menu__link">2FA Security</a>
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

    <div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card custom--card">
            <div class="card-header">
                <h5 class="card-title">@lang('Change Password')</h5>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">@lang('Current Password')</label>
                        <input type="password" class="form--control" name="current_password" required autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">@lang('Password')</label>
                        <input type="password" class="form--control @if(gs("secure_password")) secure-password @endif" name="password" required autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">@lang('Confirm Password')</label>
                        <input type="password" class="form-control form--control" name="password_confirmation" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@if(gs("secure_password"))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
