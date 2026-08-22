@php
    $user = auth()->user();
@endphp
<div class="dashboard-header">
    <div class="dashboard-header__inner" style="display: flex !important; flex-wrap: nowrap !important; align-items: center !important; justify-content: space-between !important; gap: 8px !important; width: 100% !important;">
        <div class="dashboard-header__left" style="display: flex !important; align-items: center !important; flex: 0 0 auto !important;">
            <a href="{{ route('user.deposit.history') }}" class="btn btn--success btn--sm d-flex align-items-center gap-2" style="width: max-content;">
                <i class="las la-wallet"></i> 
                <span class="d-none d-sm-inline">@lang('Deposit')</span>
            </a>
        </div>
        <div class="dashboard-header__right" style="display: flex !important; align-items: center !important; gap: 6px !important; flex: 0 0 auto !important;">
            <a href="{{ route('binary') }}" target="_blank" class="btn btn--base outline btn--sm trade-btn">
                <i class="las la-lg la-chart-line"></i> 
                <span class="d-none d-sm-inline">@lang('BINARY TRADE')</span>
                <span class="d-inline d-sm-none">@lang('BINARY')</span>
            </a>
            <a href="{{ route('trade') }}" target="_blank" class="btn btn--base outline btn--sm trade-btn">
                <span class="icon-trade"></span> 
                <span class="d-none d-sm-inline">@lang('SPOT TRADE')</span>
                <span class="d-inline d-sm-none">@lang('SPOT')</span>
            </a>
            <div class="user-info">
                <div class="user-info__right">
                    <div class="user-info__button">
                        <div class="user-info__profile">
                            <span class="d-inline d-sm-none"><i class="far fa-user-circle fs-4" style="color: var(--vn-text-secondary);"></i></span>
                            <p class="user-info__name d-none d-sm-block">{{ __($user->username) }}</p>
                        </div>
                    </div>
                </div>
                <ul class="user-info-dropdown">
                    <li class="user-info-dropdown__item">
                        <a class="user-info-dropdown__link" href="{{ route('user.profile.setting') }}">
                            <span class="icon"><i class="far fa-user-circle"></i></span>
                            <span class="text">@lang('My Profile')</span>
                        </a>
                    </li>
                    <li class="user-info-dropdown__item">
                        <a class="user-info-dropdown__link" href="{{ route('user.change.password') }}">
                            <span class="icon"><i class="fa fa-key"></i></span>
                            <span class="text">@lang('Change Password')</span>
                        </a>
                    </li>
                    <li class="user-info-dropdown__item">
                        <a class="user-info-dropdown__link" href="{{ route('user.logout') }}">
                            <span class="icon"><i class="far fa-user-circle"></i></span>
                            <span class="text">@lang('Logout')</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
