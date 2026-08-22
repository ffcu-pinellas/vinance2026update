@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center gy-4">
        <div class="col-xxl-9 col-lg-12">
            <div class="row gy-3">
                @php
                    $kycContent = getContent('kyc_content.content', true);
                @endphp
                @if ($user->kv == Status::KYC_UNVERIFIED && $user->kyc_rejection_reason)
                    <div class="col-12">
                        <div class="alert alert--danger skeleton" role="alert">
                            <div class="flex-align justify-content-between">
                                <h5 class="alert-heading text--danger mb-2">@lang('KYC Documents Rejected')</h5>
                                <button data-bs-toggle="modal" data-bs-target="#kycRejectionReason">@lang('Show Reason')</button>
                            </div>
                            <p class="mb-0">
                                {{ __(@$kycContent->data_values->rejection_content) }}
                                <a href="{{ route('user.kyc.data') }}" class="text--base">@lang('See KYC Data')</a>
                            </p>
                        </div>
                    </div>
                @endif
                @if ($user->kv == Status::KYC_UNVERIFIED && !$user->kyc_rejection_reason)
                    <div class="col-12">
                        <div class="alert alert--danger skeleton" role="alert">
                            <h5 class="alert-heading text--danger mb-2">@lang('KYC Verification Required')</h5>
                            <p class="mb-0">
                                {{ __(@$kycContent->data_values->unverified_content) }}
                                <a href="{{ route('user.kyc.form') }}" class="text--base">@lang('Click here to verify')</a>
                            </p>
                        </div>
                    </div>
                @endif
                @if ($user->kv == Status::KYC_PENDING)
                    <div class="col-12">
                        <div class="alert alert--warning flex-column justify-content-start align-items-start skeleton" role="alert">
                            <h5 class="alert-heading text--warning mb-2">@lang('KYC Verification Pending')</h5>
                            <p class="mb-0"> {{ __(@$kycContent->data_values->pending_content) }}
                                <a href="{{ route('user.kyc.data') }}" class="text--base">@lang('See KYC Data')</a>
                            </p>
                        </div>
                    </div>
                @endif
                @if (!$user->ts)
                    <div class="col-12">
                        <div class="alert-item 2fa-notice skeleton">
                            <span class="delete-icon skeleton" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete">
                                <i class="las la-times"></i></span>
                            <div class="alert flex-align alert--danger remove-2fa-notice" role="alert">
                                <span class="alert__icon">
                                    <i class="fas fa-exclamation"></i>
                                </span>
                                <div class="alert__content">
                                    <span class="alert__title">
                                        @lang('To secure your account add 2FA verification').
                                        <a href="{{ route('user.twofactor') }}" class="text--base text--small">@lang('Enable')</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-12">
                    <div class="portfolio-hero-card mb-4 skeleton">
                        <div class="portfolio-hero__inner d-flex flex-wrap align-items-center justify-content-between">
                            <div class="portfolio-hero__balance">
                                <span class="portfolio-hero__label">@lang('Estimated Total Balance')</span>
                                <h2 class="portfolio-hero__amount">
                                    {{ showAmount($estimatedBalance, currencyFormat: false) }} <span class="portfolio-hero__currency">{{ __($general->cur_text) }}</span>
                                </h2>
                            </div>
                            <div class="portfolio-hero__actions d-flex flex-wrap gap-3 mt-3 mt-sm-0">
                                <button class="btn btn--success btn--lg portfolio-btn deposit-btn-trigger">
                                    <span class="icon"><i class="las la-arrow-down"></i></span> @lang('Deposit')
                                </button>
                                <button class="btn btn--dark btn--lg portfolio-btn withdraw-btn-trigger">
                                    <span class="icon"><i class="las la-arrow-up"></i></span> @lang('Withdraw')
                                </button>
                                <a href="{{ route('user.coin.swap') }}" class="btn btn--dark btn--lg portfolio-btn swap-btn-trigger">
                                    <span class="icon"><i class="las la-exchange-alt"></i></span> @lang('Swap')
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card-wrapper">
                        <div class="row gy-4 mb-4 justify-content-center">
                            <div class="col-xxl-3 col-sm-6">
                                <a href="{{ route('user.order.open') }}" class="dashboard-widget skeleton">
                                    <div class="dashboard-widget__header">
                                        <h6 class="dashboard-widget__title">@lang('Open Orders')</h6>
                                        <span class="dashboard-widget__icon text--base bg--base-subtle">
                                            <i class="las la-spinner"></i>
                                        </span>
                                    </div>
                                    <h3 class="dashboard-widget__value">{{ getAmount($widget['open_order']) }}</h3>
                                </a>
                            </div>
                            <div class="col-xxl-3 col-sm-6">
                                <a href="{{ route('user.order.completed') }}" class="dashboard-widget skeleton">
                                    <div class="dashboard-widget__header">
                                        <h6 class="dashboard-widget__title">@lang('Completed')</h6>
                                        <span class="dashboard-widget__icon text--success bg--success-subtle">
                                            <i class="las la-check-circle"></i>
                                        </span>
                                    </div>
                                    <h3 class="dashboard-widget__value">{{ getAmount($widget['completed_order']) }}</h3>
                                </a>
                            </div>
                            <div class="col-xxl-3 col-sm-6">
                                <a href="{{ route('user.order.canceled') }}" class="dashboard-widget skeleton">
                                    <div class="dashboard-widget__header">
                                        <h6 class="dashboard-widget__title">@lang('Canceled')</h6>
                                        <span class="dashboard-widget__icon text--danger bg--danger-subtle">
                                            <i class="las la-times-circle"></i>
                                        </span>
                                    </div>
                                    <h3 class="dashboard-widget__value">{{ getAmount($widget['canceled_order']) }}</h3>
                                </a>
                            </div>
                            <div class="col-xxl-3 col-sm-6">
                                <a href="{{ route('user.trade.history') }}" class="dashboard-widget skeleton">
                                    <div class="dashboard-widget__header">
                                        <h6 class="dashboard-widget__title">@lang('Total Trades')</h6>
                                        <span class="dashboard-widget__icon text--warning bg--warning-subtle">
                                            <span class="icon-trade"></span>
                                        </span>
                                    </div>
                                    <h3 class="dashboard-widget__value">{{ getAmount($widget['total_trade']) }}</h3>
                                </a>
                            </div>
                        </div>
                        <div class="row gy-4 mb-3 justify-content-center">
                            <div class="col-lg-6">
                                <div class="transection h-100">
                                    <h5 class="transection__title skeleton"> @lang('Recent Order') </h5>
                                    @forelse ($recentOrders as $recentOrder)
                                        <div class="transection__item skeleton">
                                            <div class="d-flex flex-wrap align-items-center">
                                                <div class="transection__date">
                                                    <h6 class="transection__date-number text-white">
                                                        {{ showDateTime($recentOrder->created_at, 'd') }}
                                                    </h6>
                                                    <span class="transection__date-text">
                                                        {{ __(strtoupper(showDateTime($recentOrder->created_at, 'M'))) }}
                                                    </span>
                                                </div>
                                                <div class="transection__content">
                                                    <h6 class="transection__content-title">
                                                        @php echo $recentOrder->orderSideBadge; @endphp
                                                    </h6>
                                                    <p class="transection__content-desc">
                                                        @lang('Placed an order in the ')
                                                        {{ @$recentOrder->pair->symbol }} @lang('pair to')
                                                        {{ __(strtolower(strip_tags($recentOrder->orderSideBadge))) }}
                                                        {{ showAmount($recentOrder->amount, currencyFormat: false) }}
                                                        {{ @$recentOrder->pair->coin->symbol }}
                                                    </p>
                                                </div>
                                            </div>
                                            @php echo $recentOrder->statusBadge; @endphp
                                        </div>
                                    @empty
                                        <div class="transection__item justify-content-center p-5 skeleton">
                                            <div class="empty-thumb text-center">
                                                <img src="{{ asset('assets/images/extra_images/empty.png') }}" />
                                                <p class="fs-14">@lang('No order found')</p>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="transection h-100">
                                    <h5 class="transection__title skeleton"> @lang('Recent Transactions') </h5>
                                    @forelse ($recentTransactions as $recentTransaction)
                                        <div class="transection__item skeleton">
                                            <div class="d-flex flex-wrap align-items-center">
                                                <div class="transection__date">
                                                    <h6 class="transection__date-number text-white">
                                                        {{ showDateTime($recentTransaction->created_at, 'd') }}
                                                    </h6>
                                                    <span class="transection__date-text">
                                                        {{ __(strtoupper(showDateTime($recentTransaction->created_at, 'M'))) }}
                                                    </span>
                                                </div>
                                                <div class="transection__content">
                                                    <h6 class="transection__content-title">
                                                        {{ __(ucwords(keyToTitle($recentTransaction->remark))) }}
                                                    </h6>
                                                    <p class="transection__content-desc">
                                                        {{ __($recentTransaction->details) }}
                                                    </p>
                                                </div>
                                            </div>
                                            @if ($recentTransaction->trx_type == '+')
                                                <span class="badge badge--success">
                                                    @lang('Plus')
                                                </span>
                                            @else
                                                <span class="badge badge--danger">
                                                    @lang('Minus')
                                                </span>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="transection__item justify-content-center p-5 skeleton">
                                            <div class="empty-thumb text-center">
                                                <img src="{{ asset('assets/images/extra_images/empty.png') }}" />
                                                <p class="fs-14">@lang('No transactions found')</p>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3">
            <div class="dashboard-right">
                <div class="right-sidebar">
                    <div class="right-sidebar__header mb-3 skeleton">
                        <div class="d-flex flex-between flex-wrap">
                            <div>
                                <h4 class="mb-0 fs-18">@lang('My Assets')</h4>
                                <p class="mt-0 fs-12 text-muted">@lang('Overview of your crypto balances')</p>
                            </div>
                            <span class="toggle-dashboard-right dashboard--popup-close"><i class="las la-times"></i></span>
                        </div>
                    </div>
                    
                    <div class="right-sidebar__menu">
                        <div class="wallet-wrapper">
                            @forelse ($wallets as $wallet)
                                <div class="right-sidebar__item flex-wrap wallet-list skeleton">
                                    <div class="d-flex align-items-center">
                                        <span class="right-sidebar__item-icon">
                                            <img src="{{ @$wallet->currency->image_url }}">
                                        </span>
                                        <h6 class="right-sidebar__item-name">
                                            {{ strLimit(@$wallet->currency->name, 10) }}
                                            <span class="fs-11 d-block text-muted">
                                                {{ @$wallet->currency->symbol }}
                                            </span>
                                        </h6>
                                    </div>
                                    <h6 class="right-sidebar__item-number"> {{ showAmount($wallet->balance, currencyFormat: false) }} </h6>
                                </div>
                            @empty
                            @endforelse
                        </div>
                        <button type="button" class="w-100 show-more-wallet right-sidebar__button skeleton mt-2">
                            <span class="right-sidebar__button-icon">
                                <i class="las la-chevron-circle-down"></i>
                            </span> @lang('Show More')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-flexible-view :view="$activeTemplate . 'user.components.canvas.deposit'" :meta="['gateways' => $gateways]" />
    <x-flexible-view :view="$activeTemplate . 'user.components.canvas.withdraw'" :meta="['withdrawMethods' => $withdrawMethods]" />

    @if ($user->kv == Status::KYC_UNVERIFIED && $user->kyc_rejection_reason)
        <div class="modal fade custom--modal" id="kycRejectionReason">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                        <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </span>
                    </div>
                    <div class="modal-body">
                        <p>{{ auth()->user()->kyc_rejection_reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script')
<script>
    "use strict";
    (function($) {
        // Enhanced iOS detection
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                     (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        
        // Mobile-optimized Select2 initialization
        function initSelect2() {
            $('.currency_list, #withdraw_currency_list').select2({
                dropdownParent: $('#currency_list_wrapper, #withdraw_currency_list_wrapper'),
                minimumResultsForSearch: 5,
                templateResult: function(currency) {
                    if (!currency.id) return currency.text;
                    return $(
                        '<span class="select2-currency-option">' +
                        '<img src="' + $(currency.element).data('image') + '" class="select2-currency-img" /> ' +
                        currency.text +
                        '</span>'
                    );
                },
                templateSelection: function(currency) {
                    if (!currency.id) return currency.text;
                    return $(
                        '<span class="select2-selected-currency">' +
                        '<img src="' + $(currency.element).data('image') + '" class="select2-selected-img" /> ' +
                        currency.text +
                        '</span>'
                    );
                }
            });

            // iOS-specific fixes
            if (isIOS) {
                // Add touchstart event for iOS
                $('.select2-container').on('touchstart', '.select2-selection', function(e) {
                    $(this).closest('.select2-container').find('.select2-selection__rendered').trigger('click');
                });

                // Force dropdown to open on focus for iOS
                $('.currency_list, #withdraw_currency_list').on('select2:open', function() {
                    setTimeout(() => {
                        $('.select2-dropdown').addClass('ios-select2-dropdown');
                        $('.select2-search__field').trigger('focus');
                    }, 100);
                });

                // Close handler for iOS
                $('.currency_list, #withdraw_currency_list').on('select2:close', function() {
                    $('body').css('overflow', 'auto');
                });
            }
        }

        // Initialize on document ready
        $(document).ready(function() {
            initSelect2();
            
            // Reinitialize when modals are shown
            $(document).on('shown.bs.modal shown.bs.offcanvas', function() {
                setTimeout(initSelect2, 200);
            });

            $('.2fa-notice').on('click', '.delete-icon', function(e) {
                $(this).closest('.col-12').fadeOut('slow', function() {
                    $(this).remove();
                });
            });

            let walletSkip = 3;

            $('.show-more-wallet').on('click', function(e) {
                let route = "{{ route('user.more.wallet', ':skip') }}";
                let $this = $(this);
                $.ajax({
                    url: route.replace(':skip', walletSkip),
                    type: "GET",
                    dataType: 'json',
                    cache: false,
                    beforeSend: function() {
                        $this.html(`
                        <span class="right-sidebar__button-icon">
                            <i class="las la-spinner la-spin"></i>
                        </span>`).attr('disabled', true);
                    },
                    complete: function(e) {
                        setTimeout(() => {
                            $this.html(`
                        <span class="right-sidebar__button-icon">
                            <i class="las la-chevron-circle-down"></i>
                        </span>@lang('Show More')`).attr('disabled', false);
                            $('.wallet-list').removeClass('skeleton');
                        }, 500);
                    },
                    success: function(resp) {
                        if (resp.success && (resp.wallets && resp.wallets.length > 0)) {
                            let html = "";
                            $.each(resp.wallets, function(i, wallet) {
                                html += `
                            <div class="right-sidebar__item wallet-list skeleton">
                                <div class="d-flex align-items-center">
                                    <span class="right-sidebar__item-icon">
                                        <img src="${wallet.currency.image_url}">
                                    </span>
                                    <h6 class="right-sidebar__item-name">
                                        ${wallet.currency.name}
                                        <span class="fs-11 d-block">
                                            ${wallet.currency.symbol}
                                        </span>
                                    </h6>
                                </div>

                                <h6 class="right-sidebar__item-number">${getAmount(wallet.balance)}</h6>
                            </div>
                            `
                            });
                            walletSkip += 3;
                            $('.wallet-wrapper').append(html);
                        } else {
                            $this.remove();
                        }

                        $('.right-sidebar__menu').animate({
                            scrollTop: $('.right-sidebar__menu')[0].scrollHeight + 150
                        }, "slow");
                    },
                    error: function() {
                        notify('error', "@lang('Something went to wrong')");
                        $this.remove();
                    }
                });
            });

            // Trigger Deposit Canvas from top hero
            $(document).on('click', '.deposit-btn-trigger', function(e) {
                e.preventDefault();
                $('#depositCanvas').addClass('show');
                $('body').addClass('canvas-open');
                $('.dashboard-right').animate({ opacity: 0 }, 300, function() { $(this).css('visibility', 'hidden'); });
            });

            // Trigger Withdraw Canvas from top hero
            $(document).on('click', '.withdraw-btn-trigger', function(e) {
                e.preventDefault();
                $('#withdrawCanvas').addClass('show');
                $('body').addClass('canvas-open');
                $('.dashboard-right').animate({ opacity: 0 }, 300, function() { $(this).css('visibility', 'hidden'); });
            });

            // Handle deposit form submission
            $(document).on('submit', '.deposit-form', function(e) {
                e.preventDefault();
                
                // Hide only the right sidebar (wallet overview)
                $('.dashboard-right').animate({
                    opacity: 0
                }, 300, function() {
                    $(this).css('visibility', 'hidden');
                });
                
                // Get form values
                const amount = $(this).find('input[name="amount"]').val();
                const currency = $(this).find('.currency_list').val();
                
                // Open deposit canvas
                $('#depositCanvas').addClass('show');
                $('body').addClass('canvas-open');
                
                // Pre-fill values if any
                if (amount) {
                    $('#depositCanvas').find('input[name="amount"]').val(amount);
                }
                if (currency) {
                    $('#depositCanvas').find('.currency_list').val(currency).trigger('change');
                }
            });

            // Handle withdraw form submission
            $(document).on('submit', '.withdraw-form', function(e) {
                e.preventDefault();
                
                // Hide only the right sidebar (wallet overview)
                $('.dashboard-right').animate({
                    opacity: 0
                }, 300, function() {
                    $(this).css('visibility', 'hidden');
                });
                
                // Get form values
                const amount = $(this).find('input[name="amount"]').val();
                const currency = $(this).find('#withdraw_currency_list').val();
                
                // Open withdraw canvas
                $('#withdrawCanvas').addClass('show');
                $('body').addClass('canvas-open');
                
                // Pre-fill values if any
                if (amount) {
                    $('#withdrawCanvas').find('input[name="amount"]').val(amount);
                }
                if (currency) {
                    $('#withdrawCanvas').find('.currency_list').val(currency).trigger('change');
                }
            });

            // When closing the deposit/withdraw canvas, show the wallet overview again
            $(document).on('click', '[data-bs-dismiss="canvas"], .canvas-backdrop', function() {
                $('.dashboard-right').css('visibility', 'visible').animate({
                    opacity: 1
                }, 300);
            });
        });
    })(jQuery);
</script>
@endpush

@push('style')
<style>
    /* iOS-specific fixes */
    @supports (-webkit-touch-callout: none) {
        select, textarea, input, .form--control {
            font-size: 16px !important;
        }
        
        .select2-container .select2-selection--single {
            height: 44px !important;
            line-height: 44px !important;
        }
        
        .ios-select2-dropdown {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            width: 100% !important;
            max-height: 50vh !important;
            border-radius: 10px 10px 0 0 !important;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.1) !important;
            border: none !important;
            transform: none !important;
        }
        
        .select2-results__option {
            padding: 12px 20px !important;
            font-size: 16px !important;
        }
        
        .form--control {
            font-size: 16px !important;
        }
    }

    /* Currency selection styling */
    .select2-currency-img, .select2-selected-img {
        width: 20px;
        height: 20px;
        margin-right: 8px;
        vertical-align: middle;
    }
    
    .select2-currency-option, .select2-selected-currency {
        display: flex;
        align-items: center;
    }

    /* Dashboard styling */
    .dashboard-right {
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .right-sidebar {
            max-height: 90vh;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            position: relative;
        }
        
        .right-sidebar__deposit {
            overflow-y: visible;
            min-height: auto;
            padding-bottom: 20px;
        }
        
        .select2-container--open .select2-dropdown {
            max-height: 200px !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        
        .select2-results {
            max-height: 180px !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        
        .dashboard--popup {
            z-index: 1040;
        }
        
        #depositCanvas,
        #withdrawCanvas {
            z-index: 1050;
        }
        
        .canvas-backdrop {
            z-index: 1045;
        }
        
        .deposit__button,
        .right-sidebar__button {
            min-height: 48px;
            padding: 12px 20px;
        }
        
        .right-sidebar__menu {
            max-height: 300px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
        }
        
        .right-sidebar form {
            padding-bottom: 15px;
        }
        
        .select2-container--open {
            z-index: 9999 !important;
        }
    }
    
    /* Scrollbar styling */
    .right-sidebar::-webkit-scrollbar,
    .right-sidebar__menu::-webkit-scrollbar,
    .select2-results::-webkit-scrollbar {
        width: 6px;
    }
    
    .right-sidebar::-webkit-scrollbar-track,
    .right-sidebar__menu::-webkit-scrollbar-track,
    .select2-results::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 3px;
    }
    
    .right-sidebar::-webkit-scrollbar-thumb,
    .right-sidebar__menu::-webkit-scrollbar-thumb,
    .select2-results::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 3px;
    }
    
    .right-sidebar::-webkit-scrollbar-thumb:hover,
    .right-sidebar__menu::-webkit-scrollbar-thumb:hover,
    .select2-results::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 0, 0, 0.5);
    }
</style>
@endpush

@push('topContent')
    <h4 class="mb-4">{{ __($pageTitle) }}</h4>
@endpush