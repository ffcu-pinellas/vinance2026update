<div class="sidebar-menu">
    <div class="sidebar-menu__inner">
        <span class="sidebar-menu__close d-xl-none d-block"><i class="fas fa-times"></i></span>
        <div class="sidebar-logo">
            <a href="{{ route('home') }}" class="sidebar-logo__link">
                <img src="{{ siteLogo() }}" alt="{{ gs('site_name') }}">
            </a>
        </div>
        
        <ul class="sidebar-menu-list">
            <!-- MAIN SECTION -->
            <li class="sidebar-menu-header">
                <span>@lang('MAIN')</span>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.home') }}" class="sidebar-menu-list__link {{ menuActive('user.home') }}">
                    <span class="icon"><i class="las la-chart-pie"></i></span>
                    <span class="text">@lang('Dashboard')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.wallet.list', 'spot') }}" class="sidebar-menu-list__link {{ menuActive('user.wallet.*') }}">
                    <span class="icon"><i class="las la-wallet"></i></span>
                    <span class="text">@lang('Manage Wallet')</span>
                </a>
            </li>

            <!-- TRADING SECTION -->
            <li class="sidebar-menu-header">
                <span>@lang('TRADING')</span>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('trade') }}" target="_blank" class="sidebar-menu-list__link">
                    <span class="icon"><i class="las la-exchange-alt"></i></span>
                    <span class="text">@lang('Spot Trading')</span>
                    <span class="badge badge--primary ms-auto fs-10">@lang('Live')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('binary') }}" target="_blank" class="sidebar-menu-list__link">
                    <span class="icon"><i class="las la-chart-line"></i></span>
                    <span class="text">@lang('Binary Trading')</span>
                    <span class="badge badge--success ms-auto fs-10">@lang('Pro')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.order.open') }}" class="sidebar-menu-list__link {{ menuActive('user.order.*') }}">
                    <span class="icon"><i class="las la-list-alt"></i></span>
                    <span class="text">@lang('Manage Orders')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.trade.history') }}" class="sidebar-menu-list__link {{ menuActive('user.trade.history') }}">
                    <span class="icon"><i class="las la-history"></i></span>
                    <span class="text">@lang('Trade History')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.binary.trade.all') }}" class="sidebar-menu-list__link {{ menuActive('user.binary.trade.*') }}">
                    <span class="icon"><i class="las la-hourglass-half"></i></span>
                    <span class="text">@lang('Binary History')</span>
                </a>
            </li>

            <!-- EARN & BOTS SECTION -->
            <li class="sidebar-menu-header">
                <span>@lang('EARN & BOTS')</span>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.ai.trader') }}" class="sidebar-menu-list__link {{ menuActive('user.ai.trader*') }}">
                    <span class="icon"><i class="fas fa-robot"></i></span>
                    <span class="text">@lang('AI Auto-Trader')</span>
                    <span class="badge badge--base ms-auto fs-10">@lang('AI Bot')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.coin.swap') }}" class="sidebar-menu-list__link {{ menuActive('user.coin.swap*') }}">
                    <span class="icon"><i class="las la-sync-alt"></i></span>
                    <span class="text">@lang('Coin Swap')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.staking.index') }}" class="sidebar-menu-list__link {{ menuActive('user.staking*') }}">
                    <span class="icon"><i class="las la-coins"></i></span>
                    <span class="text">@lang('Coin Staking')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.p2p.dashboard') }}" class="sidebar-menu-list__link {{ menuActive('user.p2p*') }}">
                    <span class="icon"><i class="las la-users"></i></span>
                    <span class="text">@lang('P2P Express')</span>
                </a>
            </li>

            <!-- FINANCE & HISTORY -->
            <li class="sidebar-menu-header">
                <span>@lang('FINANCE & HISTORY')</span>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.deposit.history') }}" class="sidebar-menu-list__link {{ menuActive('user.deposit.*') }}">
                    <span class="icon"><i class="las la-arrow-circle-down"></i></span>
                    <span class="text">@lang('Deposit History')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.withdraw.history') }}" class="sidebar-menu-list__link {{ menuActive('user.withdraw.history') }}">
                    <span class="icon"><i class="las la-arrow-circle-up"></i></span>
                    <span class="text">@lang('Withdraw History')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.transactions') }}" class="sidebar-menu-list__link {{ menuActive('user.transactions') }}">
                    <span class="icon"><i class="las la-receipt"></i></span>
                    <span class="text">@lang('Transactions')</span>
                </a>
            </li>

            <!-- ACCOUNT & SUPPORT -->
            <li class="sidebar-menu-header">
                <span>@lang('ACCOUNT & SUPPORT')</span>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.referrals') }}" class="sidebar-menu-list__link {{ menuActive('user.referrals') }}">
                    <span class="icon"><i class="las la-user-friends"></i></span>
                    <span class="text">@lang('Affiliation / Invite')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('user.twofactor') }}" class="sidebar-menu-list__link {{ menuActive('user.twofactor') }}">
                    <span class="icon"><i class="las la-shield-alt"></i></span>
                    <span class="text">@lang('Security & 2FA')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item">
                <a href="{{ route('ticket.index') }}" class="sidebar-menu-list__link {{ menuActive('ticket.*') }}">
                    <span class="icon"><i class="las la-headset"></i></span>
                    <span class="text">@lang('Support Center')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item mt-3">
                <a href="{{ route('user.logout') }}" class="sidebar-menu-list__link text--danger">
                    <span class="icon"><i class="las la-sign-out-alt"></i></span>
                    <span class="text">@lang('Log Out')</span>
                </a>
            </li>
        </ul>
    </div>
</div>