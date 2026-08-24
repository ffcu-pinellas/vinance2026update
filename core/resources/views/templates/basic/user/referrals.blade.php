@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="affiliate-terminal-wrapper pb-5">
    <!-- Top Action Row: Back Button & System Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('user.home') }}" class="btn btn-outline--light btn-sm rounded-pill px-3 py-1 text--small d-inline-flex align-items-center">
            <i class="las la-arrow-left me-1"></i> <span>@lang('Dashboard')</span>
        </a>
        <span class="badge badge--success-soft rounded-pill px-3 py-1 text--small d-inline-flex align-items-center gap-1">
            <span class="live-pulse-dot"></span> @lang('Institutional Partner Network Online')
        </span>
    </div>

    <!-- Main Header Card -->
    <div class="affiliate-header-card bg--dark-two p-3 p-md-4 rounded-4 mb-4 border-0 shadow-sm">
        <div class="row align-items-center g-3">
            <div class="col-lg-6">
                <h3 class="text-white fw-bold mb-1 fs-4 d-flex align-items-center gap-2">
                    <i class="las la-users-cog text--base"></i> @lang('Affiliate & Referral Hub')
                    <span class="badge badge--primary-soft rounded-pill text--small fw-normal px-2 py-1">
                        @lang('Multi-Tier Rebates')
                    </span>
                </h3>
                <p class="text-muted text--small mb-0">@lang('Invite institutional & retail traders to earn continuous automated trading fee commission rebates.')</p>
                @if ($user->referrer)
                    <small class="text--base d-block mt-1">
                        <i class="las la-user-check"></i> @lang('Referred by'): <strong>{{ @$user->referrer->fullname }} ({{ @$user->referrer->username }})</strong>
                    </small>
                @endif
            </div>

            <!-- Shareable Referral Link & Code Box -->
            <div class="col-lg-6">
                <div class="bg--dark-three p-3 rounded-3 border border-dark">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted text--small text-uppercase fw-semibold">@lang('Your Exclusive Referral Link')</span>
                        <span class="text-muted text--small font-mono">Code: <strong class="text--base">{{ $user->username }}</strong></span>
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control bg--dark-two text-white border-dark text--small font-mono" id="refLinkInput" value="{{ $referralLink }}" readonly>
                        <button class="btn btn--base px-3" type="button" id="copyRefLinkBtn">
                            <i class="las la-copy me-1"></i> <span id="copyBtnText">@lang('Copy')</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile View Tab Switcher -->
    <div class="d-md-none mb-3">
        <div class="affiliate-mobile-nav p-1 rounded-pill bg--dark-two d-flex shadow-sm">
            <button type="button" class="btn btn-sm text-white flex-fill rounded-pill py-2 active mobile-affiliate-tab-btn" data-target="#referredUsersSection">
                <i class="las la-users me-1"></i> @lang('Referees') ({{ $totalDirect }})
            </button>
            <button type="button" class="btn btn-sm text-muted flex-fill rounded-pill py-2 mobile-affiliate-tab-btn" data-target="#commissionsSection">
                <i class="las la-wallet me-1"></i> @lang('Earnings')
            </button>
            <button type="button" class="btn btn-sm text-muted flex-fill rounded-pill py-2 mobile-affiliate-tab-btn" data-target="#networkTreeSection">
                <i class="las la-sitemap me-1"></i> @lang('Tree')
            </button>
        </div>
    </div>

    <!-- KPI Overview Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- Total Referrals -->
        <div class="col-xl-3 col-6">
            <div class="affiliate-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Direct Referees')</span>
                    <div class="affiliate-icon-badge bg-primary-soft text--base d-none d-sm-flex">
                        <i class="las la-user-friends"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1 fs-5 fs-sm-4 font-mono">{{ $totalDirect }}</h3>
                <div class="d-flex align-items-center text--small text-muted">
                    <span class="live-pulse-dot me-1"></span> @lang('Active Traders')
                </div>
            </div>
        </div>

        <!-- Total Commissions Earned -->
        <div class="col-xl-3 col-6">
            <div class="affiliate-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Total Rebates Paid')</span>
                    <div class="affiliate-icon-badge bg-success-soft text--success d-none d-sm-flex">
                        <i class="las la-hand-holding-usd"></i>
                    </div>
                </div>
                <h3 class="text--success fw-bold mb-1 fs-5 fs-sm-4 font-mono">+${{ number_format($totalCommissions, 2) }}</h3>
                <div class="d-flex align-items-center text--small text-muted">
                    <span class="badge badge--success-soft rounded-pill px-2">@lang('Instant Credited')</span>
                </div>
            </div>
        </div>

        <!-- Commission Tiers -->
        <div class="col-xl-3 col-6">
            <div class="affiliate-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Network Depth')</span>
                    <div class="affiliate-icon-badge bg-warning-soft text--warning d-none d-sm-flex">
                        <i class="las la-layer-group"></i>
                    </div>
                </div>
                <h3 class="text-white fw-bold mb-1 fs-5 fs-sm-4 font-mono">{{ $referralTiers->count() > 0 ? $referralTiers->count() : 3 }} @lang('Tiers')</h3>
                <div class="d-flex align-items-center text--small text-muted">
                    <i class="las la-check-circle text--success me-1"></i> @lang('Multi-Level Active')
                </div>
            </div>
        </div>

        <!-- Affiliate Tier Level -->
        <div class="col-xl-3 col-6">
            <div class="affiliate-metric-card h-100 p-3 rounded-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted text--small text-uppercase fw-semibold">@lang('Partner Tier')</span>
                    <div class="affiliate-icon-badge bg-info-soft text--info d-none d-sm-flex">
                        <i class="las la-crown"></i>
                    </div>
                </div>
                <h3 class="text--base fw-bold mb-1 fs-5 fs-sm-4 font-mono">VIP Partner</h3>
                <div class="d-flex align-items-center text--small text-muted">
                    <i class="las la-bolt text--warning me-1"></i> @lang('Lifetime Commissions')
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Tier Rebate Rates Overview -->
    @if($referralTiers->count() > 0)
        <div class="row g-3 mb-4">
            @foreach($referralTiers as $tier)
                <div class="col-md-4">
                    <div class="bg--dark-two p-3 rounded-3 border border-dark d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge badge--primary-soft rounded-pill px-2 py-1 text-uppercase text--small mb-1 d-inline-block">
                                @lang('Tier') {{ $tier->level }}
                            </span>
                            <h6 class="text-white mb-0 fw-bold">Level {{ $tier->level }} Rebate</h6>
                        </div>
                        <h4 class="text--success fw-bold mb-0 font-mono">{{ $tier->percent }}%</h4>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        <!-- Direct Referees List -->
        <div id="referredUsersSection" class="affiliate-content-section col-12 col-lg-7">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4">
                    <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                        <i class="las la-users text--base"></i> @lang('My Referred Traders')
                    </h5>
                    <span class="badge badge--dark rounded-pill font-mono">{{ $totalDirect }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0 custom-affiliate-table">
                            <thead>
                                <tr>
                                    <th class="ps-3 ps-sm-4">@lang('Trader')</th>
                                    <th>@lang('Joined Date')</th>
                                    <th class="text-center pe-3 pe-sm-4">@lang('Status')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($directReferrals as $referee)
                                    <tr>
                                        <td class="ps-3 ps-sm-4">
                                            <span class="text-white fw-bold">{{ $referee->fullname }}</span>
                                            <br>
                                            <small class="text-muted font-mono">@<span>{{ $referee->username }}</span></small>
                                        </td>
                                        <td class="font-mono">
                                            <span class="text-white">{{ $referee->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td class="text-center pe-3 pe-sm-4">
                                            <span class="badge badge--success-soft rounded-pill px-3 py-1 font-mono">
                                                <span class="live-pulse-dot me-1"></span> @lang('ACTIVE')
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">
                                            <i class="las la-user-plus fs-2 mb-2 d-block"></i>
                                            @lang('No referees registered yet. Share your referral link to start earning.')
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($directReferrals->hasPages())
                    <div class="card-footer bg-transparent border-top border-dark py-3 px-4">
                        {{ paginateLinks($directReferrals) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Commission Earnings Log -->
        <div id="commissionsSection" class="affiliate-content-section col-12 col-lg-5">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom border-dark d-flex justify-content-between align-items-center py-3 px-3 px-sm-4">
                    <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                        <i class="las la-receipt text--base"></i> @lang('Commission Rebate History')
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0 custom-affiliate-table">
                            <thead>
                                <tr>
                                    <th class="ps-3 ps-sm-4">@lang('Date')</th>
                                    <th>@lang('Details')</th>
                                    <th class="text-end pe-3 pe-sm-4">@lang('Amount')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($commissionLogs as $log)
                                    <tr>
                                        <td class="ps-3 ps-sm-4 font-mono text-nowrap">
                                            <span class="text-white">{{ $log->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted d-block">{{ $log->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="text-muted text--small">{{ $log->details }}</span>
                                        </td>
                                        <td class="text-end pe-3 pe-sm-4 font-mono">
                                            <span class="text--success fw-bold">+${{ number_format($log->amount, 2) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">
                                            <i class="las la-coins fs-2 mb-2 d-block"></i>
                                            @lang('No commission rebates earned yet.')
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($commissionLogs->hasPages())
                    <div class="card-footer bg-transparent border-top border-dark py-3 px-4">
                        {{ paginateLinks($commissionLogs) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Interactive Referral Network Tree -->
        <div id="networkTreeSection" class="affiliate-content-section col-12">
            <div class="card bg--dark-two border-0 rounded-4 shadow-sm">
                <div class="card-header bg-transparent border-bottom border-dark py-3 px-3 px-sm-4">
                    <h5 class="text-white mb-0 d-flex align-items-center gap-2">
                        <i class="las la-sitemap text--base"></i> @lang('Multi-Level Referral Network Tree')
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if ($user->allReferrals->count() > 0 && $maxLevel > 0)
                        <div class="treeview-container p-3 rounded-3 bg--dark-three border border-dark">
                            <ul class="treeview text-white font-mono">
                                <li class="items-expanded">
                                    <strong class="text--base">{{ $user->fullname }}</strong> ({{ $user->username }})
                                    @include($activeTemplate . 'partials.under_tree', [
                                        'user'    => $user,
                                        'layer'   => 0,
                                        'isFirst' => true,
                                    ])
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="las la-sitemap fs-1 mb-2 d-block text--base"></i>
                            <p class="mb-0">@lang('Your referral network will visually render here as your invited users start referring others.')</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'dashboard/css/jquery.treeView.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'dashboard/js/jquery.treeView.js') }}"></script>
@endpush

@push('style')
<style>
    .affiliate-terminal-wrapper {
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
    .affiliate-mobile-nav {
        border: 1px solid #334155;
    }
    .affiliate-mobile-nav .btn.active {
        background: #3b82f6 !important;
        color: #fff !important;
        font-weight: 600;
    }
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
    .affiliate-metric-card {
        background: #0f172a;
        border: 1px solid #1e293b;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .affiliate-metric-card:hover {
        transform: translateY(-2px);
        border-color: #3b82f6;
    }
    .affiliate-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .bg-primary-soft { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .bg-success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .bg-info-soft { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }
    .bg-warning-soft { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .badge--success-soft { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .badge--primary-soft { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .custom-affiliate-table th {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
        font-size: 11px;
        text-transform: uppercase;
        border: none;
    }
    .custom-affiliate-table td {
        border-bottom: 1px solid #1e293b !important;
        padding: 12px 8px;
        font-size: 13px;
    }
    .treeview-container {
        max-height: 400px;
        overflow-y: auto;
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

        // Copy Referral Link
        $('#copyRefLinkBtn').on('click', function () {
            var copyText = document.getElementById("refLinkInput");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            $('#copyBtnText').text("@lang('Copied!')");
            notify('success', '@lang("Referral link copied to clipboard!")');
            setTimeout(function() {
                $('#copyBtnText').text("@lang('Copy')");
            }, 2000);
        });

        // Initialize TreeView if available
        if ($.fn.treeView) {
            $('.treeview').treeView();
        }

        // Mobile Tabs Switcher
        function handleMobileAffiliateTabs() {
            if ($(window).width() < 768) {
                var activeTarget = $('.mobile-affiliate-tab-btn.active').data('target') || '#referredUsersSection';
                $('.affiliate-content-section').addClass('d-none');
                $(activeTarget).removeClass('d-none');
            } else {
                $('.affiliate-content-section').removeClass('d-none');
            }
        }

        handleMobileAffiliateTabs();
        $(window).on('resize', handleMobileAffiliateTabs);

        $('.mobile-affiliate-tab-btn').on('click', function() {
            $('.mobile-affiliate-tab-btn').removeClass('active text-white').addClass('text-muted');
            $(this).addClass('active text-white').removeClass('text-muted');

            var target = $(this).data('target');
            $('.affiliate-content-section').addClass('d-none');
            $(target).removeClass('d-none');
        });
    })(jQuery);
</script>
@endpush
