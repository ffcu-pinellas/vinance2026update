@extends($activeTemplate.'layouts.master')
@section('content')
<div class="dashboard-section">
    <div class="container">
        <!-- Statistics Cards -->
        <div class="row gy-4 mb-4">
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="widget-info">
                            <h3 class="widget-title">@lang('Total Staked')</h3>
                            <h4 class="widget-number">{{ showAmount($statistics['total_staked']) }} USDT</h4>
                        </div>
                        <div class="dashboard-widget__icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="widget-info">
                            <h3 class="widget-title">@lang('Total Earnings')</h3>
                            <h4 class="widget-number">{{ showAmount($statistics['total_earnings']) }} USDT</h4>
                        </div>
                        <div class="dashboard-widget__icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="widget-info">
                            <h3 class="widget-title">@lang('Active Stakes')</h3>
                            <h4 class="widget-number">{{ $statistics['active_stakes'] }}</h4>
                        </div>
                        <div class="dashboard-widget__icon">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="widget-info">
                            <h3 class="widget-title">@lang('Best APY')</h3>
                            <h4 class="widget-number">{{ $statistics['highest_apy'] }}%</h4>
                        </div>
                        <div class="dashboard-widget__icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Staking Options -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="section-header d-flex align-items-center mb-3">
                    <div class="crypto-icon">
                        <img src="{{ asset('assets/images/currency/67d332468fda71741894214.jpeg') }}" alt="USDT" class="me-2">
                    </div>
                    <h4 class="section-title mb-0">@lang('Available Staking Options')</h4>
                </div>
            </div>
            
            <div class="col-12">
                <div class="staking-pools-container">
                    @forelse($stakingPools as $pool)
                    <div class="staking-card">
                        <div class="staking-card-header">
                            <h5 class="pool-name">{{ $pool->name }}</h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-type">{{ ucfirst($pool->type) }}</span>
                                <span class="badge badge-apy">{{ $pool->apy_rate }}% APY</span>
                            </div>
                        </div>
                        <div class="staking-info">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">@lang('Min. Stake')</div>
                                    <div class="info-value">{{ showAmount($pool->configuration->min_amount) }} USDT</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">@lang('Max. Stake')</div>
                                    <div class="info-value">{{ showAmount($pool->configuration->max_amount) }} USDT</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">@lang('Total Staked')</div>
                                    <div class="info-value">{{ showAmount($pool->total_pool_staked) }} USDT</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">@lang('Stakers')</div>
                                    <div class="info-value">{{ $pool->total_participants }}</div>
                                </div>
                                @if($pool->type == 'locked')
                                <div class="info-item lock-period">
                                    <div class="info-label">@lang('Lock Period')</div>
                                    <div class="info-value">{{ $pool->lock_period_days }} @lang('Days')</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <button class="btn btn-stake stake-btn" 
                                data-pool="{{ $pool->id }}"
                                data-min="{{ showAmount($pool->configuration->min_amount) }}"
                                data-max="{{ showAmount($pool->configuration->max_amount) }}"
                                data-bs-toggle="modal"
                                data-bs-target="#stakeModal">
                            @lang('Stake Now')
                        </button>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-warning">@lang('No staking pools available')</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Active Stakes -->
        <div class="row">
            <div class="col-12">
                <div class="card active-stakes-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-chart-line me-2"></i>
                            @lang('Your Active Stakes')
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="active-stakes-container">
                            @forelse($activeStakes as $stake)
                            <div class="active-stake-item">
                                <div class="stake-info">
                                    <div class="stake-pool">
                                        <img src="{{ asset('assets/images/currency/67d332468fda71741894214.jpeg') }}" alt="USDT" class="currency-icon">
                                        <div>
                                            <span class="pool-name">{{ $stake->pool->name }}</span>
                                            <span class="pool-type">{{ ucfirst($stake->pool->type) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="stake-details">
                                        <div class="stake-data">
                                            <div class="data-label">@lang('Amount')</div>
                                            <div class="data-value">{{ showAmount($stake->principal_amount) }} USDT</div>
                                        </div>
                                        <div class="stake-data">
                                            <div class="data-label">@lang('APY')</div>
                                            <div class="data-value">{{ $stake->pool->apy_rate }}%</div>
                                        </div>
                                        <div class="stake-data">
                                            <div class="data-label">@lang('Start Date')</div>
                                            <div class="data-value">{{ showDateTime($stake->start_time) }}</div>
                                        </div>
                                        <div class="stake-data">
                                            <div class="data-label">@lang('Earned')</div>
                                            <div class="data-value earned">{{ showAmount($stake->accumulated_rewards) }} USDT</div>
                                        </div>
                                    </div>
                                    
                                    <div class="stake-actions">
                                        @if($stake->pool->type != 'locked' || Carbon\Carbon::parse($stake->start_time)->addDays($stake->pool->lock_period_days)->isPast())
                                        <button type="button" 
                                                class="btn btn-action btn-unstake unstake-btn"
                                                data-stake="{{ $stake->id }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#unstakeModal"
                                                title="Unstake">
                                            <i class="fas fa-unlock-alt"></i>
                                            <span class="action-text">Unstake</span>
                                        </button>
                                        @endif
                                        @if($stake->accumulated_rewards > 0)
                                        <button type="button"
                                                class="btn btn-action btn-compound compound-btn"
                                                data-stake="{{ $stake->id }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#compoundModal"
                                                title="Compound">
                                            <i class="fas fa-sync"></i>
                                            <span class="action-text">Compound</span>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="no-stakes">
                                <div class="empty-state">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <p>@lang('No active stakes found')</p>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @if($activeStakes->hasPages())
                    <div class="card-footer">
                        {{ $activeStakes->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include($activeTemplate . 'user.staking.partials.stake_modal')
@include($activeTemplate . 'user.staking.partials.unstake_modal')
@include($activeTemplate . 'user.staking.partials.compound_modal')


@endsection


@push('script')
<script src="{{ route('staking.js') }}"></script>
@endpush


@push('style')
<style>
    /* Base Styles */
    :root {
        --primary-color: #00ff88;
        --primary-color-rgb: 0, 255, 136;
        --secondary-color: #6610f2;
        --secondary-color-rgb: 102, 16, 242;
        --dark-bg: #121212;
        --card-bg: #1a1a1a;
        --card-bg-hover: #222222;
        --border-color: #2a2a2a;
        --text-color: #fff;
        --text-muted: #888;
        --text-light: #aaa;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --border-radius: 12px;
        --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        --transition: all 0.3s ease;
    }

    /* Dashboard Widgets */
    .dashboard-widget {
        background: linear-gradient(145deg, var(--card-bg), var(--card-bg-hover));
        border-radius: var(--border-radius);
        padding: 25px;
        margin-bottom: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        overflow: hidden;
        position: relative;
    }
    
    .dashboard-widget:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        border-color: rgba(var(--primary-color-rgb), 0.3);
    }
    
    .dashboard-widget::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .dashboard-widget__icon {
        width: 56px;
        height: 56px;
        background: rgba(var(--primary-color-rgb), 0.1);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: var(--primary-color);
        box-shadow: 0 5px 15px rgba(var(--primary-color-rgb), 0.2);
    }

    .widget-title {
        font-size: 14px;
        color: var(--text-muted);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .widget-number {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-color);
        margin: 0;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }

    /* Section Headers */
    .section-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
    }
    
    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-color);
        margin: 0;
    }
    
    .crypto-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }
    
    .crypto-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }

    /* Staking Pools */
    .staking-pools-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .staking-card {
        background: linear-gradient(145deg, var(--card-bg), var(--card-bg-hover));
        border-radius: var(--border-radius);
        padding: 25px;
        height: 100%;
        transition: var(--transition);
        border: 1px solid var(--border-color);
        box-shadow: var(--box-shadow);
        display: flex;
        flex-direction: column;
    }
    
    .staking-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        border-color: rgba(var(--primary-color-rgb), 0.3);
    }
    
    .staking-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .pool-name {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        color: var(--text-color);
    }
    
    .badge {
        padding: 8px 14px;
        font-weight: 500;
        font-size: 12px;
        border-radius: 30px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    .badge-type {
        background: rgba(var(--secondary-color-rgb), 0.15);
        color: var(--secondary-color);
        border: 1px solid rgba(var(--secondary-color-rgb), 0.3);
    }
    
    .badge-apy {
        background: rgba(var(--primary-color-rgb), 0.15);
        color: var(--primary-color);
        border: 1px solid rgba(var(--primary-color-rgb), 0.3);
    }
    
    .staking-info {
        flex-grow: 1;
        margin-bottom: 20px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .info-item {
        background: rgba(255, 255, 255, 0.03);
        padding: 15px;
        border-radius: 10px;
        transition: var(--transition);
        border: 1px solid transparent;
    }
    
    .info-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--border-color);
    }
    
    .lock-period {
        grid-column: span 2;
    }
    
    .info-label {
        color: var(--text-muted);
        font-size: 12px;
        margin-bottom: 5px;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .info-value {
        color: var(--text-color);
        font-size: 16px;
        font-weight: 600;
    }
    
    .btn-stake {
        padding: 12px 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(var(--primary-color-rgb), 0.9), rgba(var(--primary-color-rgb), 0.7));
        border: none;
        color: #161616;
        transition: var(--transition);
        font-size: 15px;
    }
    
    .btn-stake:hover {
        background: linear-gradient(135deg, var(--primary-color), rgba(var(--primary-color-rgb), 0.9));
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(var(--primary-color-rgb), 0.3);
    }

    /* Active Stakes Card */
    .active-stakes-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--box-shadow);
        overflow: hidden;
    }
    
    .active-stakes-card .card-header {
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid var(--border-color);
        padding: 20px 25px;
    }
    
    .active-stakes-card .card-title {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        color: var(--text-color);
        display: flex;
        align-items: center;
    }
    
    .active-stakes-card .card-title i {
        color: var(--primary-color);
    }
    
    .active-stakes-container {
        padding: 0;
    }
    
    .active-stake-item {
        padding: 20px 25px;
        border-bottom: 1px solid var(--border-color);
        transition: var(--transition);
    }
    
    .active-stake-item:last-child {
        border-bottom: none;
    }
    
    .active-stake-item:hover {
        background: rgba(255, 255, 255, 0.02);
    }
    
    .stake-info {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .stake-pool {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .currency-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: contain;
    }
    
    .pool-name {
        display: block;
        font-weight: 600;
        color: var(--text-color);
        font-size: 16px;
    }
    
    .pool-type {
        display: block;
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 2px;
    }
    
    .stake-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin: 10px 0;
    }
    
    .stake-data {
        background: rgba(255, 255, 255, 0.03);
        padding: 10px 15px;
        border-radius: 8px;
        transition: var(--transition);
    }
    
    .stake-data:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .data-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    
    .data-value {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-color);
    }
    
    .data-value.earned {
        color: var(--primary-color);
    }
    
    .stake-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .btn-action {
        padding: 10px 15px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition);
    }
    
    .btn-unstake {
        background: rgba(var(--danger-color), 0.15);
        color: #ff4757;
        border: 1px solid rgba(var(--danger-color), 0.3);
    }
    
    .btn-unstake:hover {
        background: rgba(var(--danger-color), 0.25);
        color: #ff6b81;
    }
    
    .btn-compound {
        background: rgba(var(--success-color), 0.15);
        color: #2ed573;
        border: 1px solid rgba(var(--success-color), 0.3);
    }
    
    .btn-compound:hover {
        background: rgba(var(--success-color), 0.25);
        color: #7bed9f;
    }

    /* Empty State */
    .no-stakes {
        padding: 50px 20px;
        text-align: center;
    }
    
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        opacity: 0.7;
    }

    /* Modals */
    .modal-content {
        background: linear-gradient(145deg, var(--card-bg), var(--card-bg-hover));
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
    }
    
    .modal-header {
        border-bottom: 1px solid var(--border-color);
        padding: 20px 25px;
    }
    
    .modal-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-color);
    }
    
    .modal-body {
        padding: 25px;
    }
    
    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 20px 25px;
    }
    
    .form-control {
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border-color);
        color: var(--text-color);
        border-radius: 8px;
        padding: 12px 15px;
        transition: var(--transition);
    }
    
    .form-control:focus {
        background-color: rgba(255, 255, 255, 0.07);
        border-color: rgba(var(--primary-color-rgb), 0.5);
        box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.15);
        color: var(--text-color);
    }
    
    .input-group-text {
        background-color: rgba(255, 255, 255, 0.03);
        border-color: var(--border-color);
        color: var(--text-muted);
        border-radius: 0 8px 8px 0;
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
        .staking-pools-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
        
        .stake-details {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .dashboard-widget {
            padding: 20px;
        }
        
        .widget-number {
            font-size: 24px;
        }
        
        .dashboard-widget__icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
        }
        
        .staking-card {
            padding: 20px;
        }
        
        .active-stake-item {
            padding: 15px 20px;
        }
        
        .stake-details {
            grid-template-columns: repeat(2, 1fr);
        }

        .btn-action .action-text {
            display: none;
        }
        
        .btn-action {
            padding: 10px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-action i {
            margin: 0;
        }
    }

    @media (max-width: 576px) {
        .stake-details {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .stake-data {
            padding: 10px;
        }
        
        .data-label {
            font-size: 11px;
        }
        
        .data-value {
            font-size: 14px;
        }
        
        .active-stake-item {
            padding: 15px;
        }
        
        .active-stakes-card .card-header {
            padding: 15px;
        }
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    
    ::-webkit-scrollbar-track {
        background: var(--card-bg);
    }
    
    ::-webkit-scrollbar-thumb {
        background: rgba(var(--primary-color-rgb), 0.3);
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: rgba(var(--primary-color-rgb), 0.5);
    }

    /* Pagination */
    .pagination {
        margin: 0;
        justify-content: center;
    }
    
    .page-item .page-link {
        background: rgba(255, 255, 255, 0.03);
        border-color: var(--border-color);
        color: var(--text-muted);
        border-radius: 8px;
        margin: 0 2px;
        padding: 8px 14px;
        transition: var(--transition);
    }
    
    .page-item.active .page-link {
        background: rgba(var(--primary-color-rgb), 0.2);
        border-color: rgba(var(--primary-color-rgb), 0.3);
        color: var(--primary-color);
    }
    
    .page-item .page-link:hover {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-color);
    }
    
    .card-footer {
        background: rgba(255, 255, 255, 0.01);
        border-top: 1px solid var(--border-color);
        padding: 15px 20px;
    }
</style>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";
        
        $('.stake-btn').on('click', function() {
            var modal = $('#stakeModal');
            var pool = $(this).data('pool');
            var min = $(this).data('min');
            var max = $(this).data('max');
            
            modal.find('input[name=pool_id]').val(pool);
            modal.find('.min-amount').text(min);
            modal.find('.max-amount').text(max);
            modal.find('input[name=principal_amount]').attr('min', min);
            modal.find('input[name=principal_amount]').attr('max', max);
        });

        $('.unstake-btn').on('click', function() {
            var modal = $('#unstakeModal');
            var stake = $(this).data('stake');
            modal.find('input[name=stake_id]').val(stake);
        });

        $('.compound-btn').on('click', function() {
            var modal = $('#compoundModal');
            var stake = $(this).data('stake');
            modal.find('input[name=stake_id]').val(stake);
        });

        // Add animation to cards on hover
        $('.dashboard-widget, .staking-card').hover(
            function() {
                $(this).addClass('active');
            },
            function() {
                $(this).removeClass('active');
            }
        );
        
        // Improve mobile interaction with touch events
        if(window.innerWidth <= 768) {
            $('.active-stake-item').on('touchstart', function() {
                $(this).addClass('touch-active');
            }).on('touchend', function() {
                setTimeout(() => {
                    $(this).removeClass('touch-active');
                }, 300);
            });
        }
        
        // Add smooth scroll for better user experience
        $('a.nav-link, a.btn').on('click', function(e) {
            if (this.hash !== '') {
                e.preventDefault();
                const hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top - 70
                }, 800);
            }
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Add custom class to active cards
        $('.staking-card').each(function() {
            if ($(this).find('.badge-apy').text().replace('%', '') > 15) {
                $(this).addClass('high-apy');
            }
        });
        
        // Enhanced mobile experience for active stakes
        if (window.innerWidth <= 576) {
            $('.active-stake-item').on('click', function() {
                $(this).toggleClass('expanded');
            });
        }
        
        // Animation for statistics
        $('.widget-number').each(function() {
            $(this).prop('Counter', 0).animate({
                Counter: $(this).text().replace(/[^0-9.]/g, '')
            }, {
                duration: 2000,
                easing: 'swing',
                step: function(now) {
                    // Only animate if it's a number
                    if (!isNaN($(this).text().replace(/[^0-9.]/g, ''))) {
                        let value = Math.ceil(now);
                        let formatted = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        
                        // If original has decimal, keep it
                        if ($(this).text().includes('.')) {
                            const decimal = $(this).text().split('.')[1].replace(/[^0-9]/g, '');
                            if (decimal) {
                                formatted += '.' + decimal;
                            }
                        }
                        
                        // Add USDT or % if present in original
                        if ($(this).text().includes('USDT')) {
                            formatted += ' USDT';
                        } else if ($(this).text().includes('%')) {
                            formatted += '%';
                        }
                        
                        $(this).text(formatted);
                    }
                }
            });
        });
    })(jQuery);
</script>
@endpush