@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-content-wrapper">
        <!-- Security & KYC Alerts -->
        @php
            $kycContent = getContent('kyc_content.content', true);
        @endphp
        @if ($user->kv == Status::KYC_UNVERIFIED && $user->kyc_rejection_reason)
            <div class="alert alert--danger d-flex flex-wrap justify-content-between align-items-center mb-4 p-3 rounded-3" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <i class="las la-exclamation-circle fs-24 text--danger"></i>
                    <div>
                        <h6 class="alert-heading text--danger mb-0">@lang('KYC Documents Rejected')</h6>
                        <p class="mb-0 fs-13 text--muted">
                            {{ __(@$kycContent->data_values->rejection_content) }}
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-2 mt-sm-0">
                    <button class="btn btn--dark btn--sm" data-bs-toggle="modal" data-bs-target="#kycRejectionReason">@lang('Show Reason')</button>
                    <a href="{{ route('user.kyc.form') }}" class="btn btn--base btn--sm">@lang('Resubmit KYC')</a>
                </div>
            </div>
        @endif
        @if ($user->kv == Status::KYC_UNVERIFIED && !$user->kyc_rejection_reason)
            <div class="alert alert--warning d-flex flex-wrap justify-content-between align-items-center mb-4 p-3 rounded-3" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <i class="las la-shield-alt fs-24 text--warning"></i>
                    <div>
                        <h6 class="alert-heading text--warning mb-0">@lang('Identity Verification Required')</h6>
                        <p class="mb-0 fs-13 text--muted">
                            @lang('Verify your identity to unlock higher withdrawal limits and all trading features.')
                        </p>
                    </div>
                </div>
                <a href="{{ route('user.kyc.form') }}" class="btn btn--base btn--sm mt-2 mt-sm-0">
                    <i class="las la-check-circle"></i> @lang('Verify Identity')
                </a>
            </div>
        @endif
        @if ($user->kv == Status::KYC_PENDING)
            <div class="alert alert--warning d-flex align-items-center gap-3 mb-4 p-3 rounded-3" role="alert">
                <i class="las la-hourglass-half fs-24 text--warning"></i>
                <div>
                    <h6 class="alert-heading text--warning mb-0">@lang('KYC Verification Under Review')</h6>
                    <p class="mb-0 fs-13 text--muted">
                        {{ __(@$kycContent->data_values->pending_content) }}
                        <a href="{{ route('user.kyc.data') }}" class="text--base ms-1">@lang('View Submitted Data')</a>
                    </p>
                </div>
            </div>
        @endif
        @if (!$user->ts)
            <div class="col-12 mb-4 2fa-notice">
                <div class="alert alert--danger d-flex justify-content-between align-items-center p-3 rounded-3" role="alert">
                    <div class="d-flex align-items-center gap-3">
                        <i class="las la-lock fs-24 text--danger"></i>
                        <div>
                            <span class="fw-600 text-white">@lang('Protect Your Account with 2FA')</span>
                            <p class="mb-0 fs-13 text--muted">@lang('Strengthen your account security by enabling Google Two-Factor Authentication.')</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('user.twofactor') }}" class="btn btn--base btn--sm">@lang('Enable 2FA')</a>
                        <button type="button" class="delete-icon btn btn--dark btn--sm p-2"><i class="las la-times"></i></button>
                    </div>
                </div>
            </div>
        @endif

        <!-- ======================= HERO ASSET OVERVIEW CARD ======================= -->
        <div class="vn-asset-card mb-4 p-4 rounded-4 position-relative overflow-hidden">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fs-13 text--muted fw-500 text-uppercase letter-spacing-1">@lang('Estimated Total Balance')</span>
                        <button type="button" class="btn-eye-toggle border-0 bg-transparent text--muted p-0 ms-1 cursor-pointer" id="toggleBalanceBtn" title="Hide/Show Balance">
                            <i class="las la-eye fs-18" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-baseline gap-3">
                        <h2 class="vn-balance-number mb-0 fw-700 text-white" id="userBalanceDisplay">
                            {{ showAmount($estimatedBalance) }} <span class="fs-18 fw-500 text--muted">USD</span>
                        </h2>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <span class="badge badge--success fs-12 px-2 py-1">
                            <i class="las la-arrow-up"></i> @lang('Spot Wallet Active')
                        </span>
                        <span class="fs-13 text--muted">
                            @lang('Approx') ≈ {{ getAmount($estimatedBalance / (gs('currency_rate') ?: 1), 4) }} {{ __(gs('cur_text')) }}
                        </span>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <button type="button" class="btn btn--base px-3 py-2 deposit-btn-trigger" data-bs-toggle="offcanvas" data-bs-target="#deposit-canvas">
                            <i class="las la-arrow-down"></i> @lang('Deposit')
                        </button>
                        <button type="button" class="btn btn--dark px-3 py-2 withdraw-btn-trigger" data-bs-toggle="offcanvas" data-bs-target="#withdraw-canvas">
                            <i class="las la-arrow-up"></i> @lang('Withdraw')
                        </button>
                        <a href="{{ route('user.coin.swap') }}" class="btn btn--dark px-3 py-2">
                            <i class="las la-sync-alt"></i> @lang('Swap')
                        </a>
                        <a href="{{ route('trade') }}" target="_blank" class="btn btn--dark px-3 py-2">
                            <i class="las la-exchange-alt"></i> @lang('Trade')
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= STATS SUMMARY ROW ======================= -->
        <div class="row g-3 mb-4">
            <div class="col-xxl-3 col-sm-6">
                <a href="{{ route('user.order.open') }}" class="vn-stat-card d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none">
                    <div>
                        <span class="fs-12 text--muted d-block mb-1">@lang('Open Orders')</span>
                        <h4 class="mb-0 text-white fw-700">{{ getAmount($widget['open_order']) }}</h4>
                    </div>
                    <div class="vn-stat-icon vn-stat-icon--primary">
                        <i class="las la-clock"></i>
                    </div>
                </a>
            </div>
            <div class="col-xxl-3 col-sm-6">
                <a href="{{ route('user.order.completed') }}" class="vn-stat-card d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none">
                    <div>
                        <span class="fs-12 text--muted d-block mb-1">@lang('Completed Orders')</span>
                        <h4 class="mb-0 text-white fw-700">{{ getAmount($widget['completed_order']) }}</h4>
                    </div>
                    <div class="vn-stat-icon vn-stat-icon--success">
                        <i class="las la-check-circle"></i>
                    </div>
                </a>
            </div>
            <div class="col-xxl-3 col-sm-6">
                <a href="{{ route('user.order.canceled') }}" class="vn-stat-card d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none">
                    <div>
                        <span class="fs-12 text--muted d-block mb-1">@lang('Canceled Orders')</span>
                        <h4 class="mb-0 text-white fw-700">{{ getAmount($widget['canceled_order']) }}</h4>
                    </div>
                    <div class="vn-stat-icon vn-stat-icon--danger">
                        <i class="las la-times-circle"></i>
                    </div>
                </a>
            </div>
            <div class="col-xxl-3 col-sm-6">
                <a href="{{ route('user.trade.history') }}" class="vn-stat-card d-flex align-items-center justify-content-between p-3 rounded-3 text-decoration-none">
                    <div>
                        <span class="fs-12 text--muted d-block mb-1">@lang('Total Spot Trades')</span>
                        <h4 class="mb-0 text-white fw-700">{{ getAmount($widget['total_trade']) }}</h4>
                    </div>
                    <div class="vn-stat-icon vn-stat-icon--warning">
                        <i class="las la-chart-bar"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- ======================= MAIN 2-COLUMN SECTION ======================= -->
        <div class="row g-4">
            <!-- Left Column: Asset Allocation & Recent Orders -->
            <div class="col-xxl-8 col-xl-7">
                <!-- Top Asset Holdings Panel -->
                <div class="vn-card p-4 rounded-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0 fs-16 fw-600 text-white">@lang('Asset Allocation & Balances')</h5>
                            <p class="fs-12 text--muted mb-0">@lang('Real-time overview of your spot and funding balances')</p>
                        </div>
                        <a href="{{ route('user.wallet.list', 'spot') }}" class="btn btn--dark btn--sm fs-12">
                            @lang('View All Wallets') <i class="las la-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>@lang('Asset')</th>
                                    <th>@lang('Available Balance')</th>
                                    <th>@lang('In Order')</th>
                                    <th class="text-end">@lang('Quick Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($wallets->take(6) as $wallet)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ @$wallet->currency->image_url }}" alt="{{ @$wallet->currency->symbol }}" class="rounded-circle" width="28" height="28">
                                                <div>
                                                    <h6 class="mb-0 fs-13 text-white">{{ @$wallet->currency->name }}</h6>
                                                    <span class="fs-11 text--muted text-uppercase">{{ @$wallet->currency->symbol }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fs-13 fw-600 text-white font-mono">
                                                {{ showAmount($wallet->balance, currencyFormat: false) }}
                                            </span>
                                            <small class="fs-11 text--muted d-block">
                                                ≈ ${{ showAmount($wallet->balance * (@$wallet->currency->rate ?: 1)) }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="fs-13 text--muted font-mono">
                                                {{ showAmount($wallet->in_order, currencyFormat: false) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <a href="{{ route('trade') }}?pair={{ @$wallet->currency->symbol }}_USDT" target="_blank" class="btn btn--dark btn--sm py-1 px-2 fs-11" title="Trade">
                                                    @lang('Trade')
                                                </a>
                                                <a href="{{ route('user.wallet.view', ['type' => $wallet->typeText, 'currencySymbol' => @$wallet->currency->symbol]) }}" class="btn btn--dark btn--sm py-1 px-2 fs-11" title="Details">
                                                    <i class="las la-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4">
                                            <span class="text--muted fs-13">@lang('No wallet balances found')</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Orders Panel -->
                <div class="vn-card p-4 rounded-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0 fs-16 fw-600 text-white">@lang('Recent Orders')</h5>
                            <p class="fs-12 text--muted mb-0">@lang('Your latest executed and pending trade orders')</p>
                        </div>
                        <a href="{{ route('user.order.open') }}" class="btn btn--dark btn--sm fs-12">
                            @lang('All Orders') <i class="las la-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="vn-order-list">
                        @forelse ($recentOrders as $recentOrder)
                            <div class="vn-order-item d-flex flex-wrap justify-content-between align-items-center p-3 mb-2 rounded-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="vn-date-badge text-center p-2 rounded-2">
                                        <span class="d-block fs-14 fw-700 text-white">{{ showDateTime($recentOrder->created_at, 'd') }}</span>
                                        <span class="d-block fs-10 text-uppercase text--muted">{{ showDateTime($recentOrder->created_at, 'M') }}</span>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            @php echo $recentOrder->orderSideBadge; @endphp
                                            <span class="fw-600 text-white fs-13">{{ @$recentOrder->pair->symbol }}</span>
                                        </div>
                                        <span class="fs-12 text--muted d-block mt-1">
                                            {{ showAmount($recentOrder->amount, currencyFormat: false) }} {{ @$recentOrder->pair->coin->symbol }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end mt-2 mt-sm-0">
                                    @php echo $recentOrder->statusBadge; @endphp
                                    <span class="fs-11 text--muted d-block mt-1">{{ showDateTime($recentOrder->created_at, 'H:i') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-5">
                                <i class="las la-inbox fs-40 text--muted mb-2"></i>
                                <p class="fs-13 text--muted mb-0">@lang('No recent orders found')</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Deposit/Withdraw & Recent Transactions -->
            <div class="col-xxl-4 col-xl-5">
                <!-- Fast Action Card: Deposit / Withdraw -->
                <div class="vn-card p-4 rounded-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fs-16 fw-600 text-white">@lang('Fast Transaction')</h5>
                        <span class="badge badge--primary fs-11">@lang('Instant')</span>
                    </div>

                    <!-- Deposit Tab Form -->
                    <div class="mb-3" id="currency_list_wrapper">
                        <label class="form--label fs-12">@lang('Quick Deposit')</label>
                        <form class="deposit-form">
                            <div class="input-group mb-2">
                                <input type="number" step="any" name="amount" class="form--control form-control" placeholder="@lang('Enter Amount')" required>
                                <div class="input-group-text">
                                    <x-currency-list :action="route('user.currency.all')" valueType="2" logCurrency="true" class="ios-select-fix" />
                                </div>
                            </div>
                            <button class="btn btn--base w-100 py-2 fs-13 fw-600" type="submit">
                                <i class="las la-arrow-circle-down me-1"></i> @lang('Proceed to Deposit')
                            </button>
                        </form>
                    </div>

                    <hr class="border-secondary opacity-25 my-3">

                    <!-- Withdraw Tab Form -->
                    <div id="withdraw_currency_list_wrapper">
                        <label class="form--label fs-12">@lang('Quick Withdraw')</label>
                        <form class="withdraw-form">
                            <div class="input-group mb-2">
                                <input type="number" name="amount" step="any" class="form--control form-control" placeholder="@lang('Enter Amount')" required>
                                <div class="input-group-text">
                                    <x-currency-list :action="route('user.currency.all')" id="withdraw_currency_list" parent="withdraw_currency_list_wrapper" valueType="2" logCurrency="true" class="ios-select-fix" />
                                </div>
                            </div>
                            <button class="btn btn--dark w-100 py-2 fs-13 fw-600" type="submit">
                                <i class="las la-arrow-circle-up me-1"></i> @lang('Request Withdrawal')
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Recent Transactions Stream -->
                <div class="vn-card p-4 rounded-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0 fs-16 fw-600 text-white">@lang('Recent Transactions')</h5>
                            <p class="fs-12 text--muted mb-0">@lang('Account balance activities')</p>
                        </div>
                        <a href="{{ route('user.transactions') }}" class="btn btn--dark btn--sm fs-12">
                            @lang('All History') <i class="las la-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="vn-transaction-list">
                        @forelse ($recentTransactions as $recentTransaction)
                            <div class="vn-transaction-item d-flex justify-content-between align-items-center p-3 mb-2 rounded-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="vn-stat-icon @if($recentTransaction->trx_type == '+') vn-stat-icon--success @else vn-stat-icon--danger @endif" style="width: 36px; height: 36px; font-size: 16px;">
                                        <i class="las @if($recentTransaction->trx_type == '+') la-plus @else la-minus @endif"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fs-13 text-white">{{ __(ucwords(keyToTitle($recentTransaction->remark))) }}</h6>
                                        <span class="fs-11 text--muted d-block">{{ showDateTime($recentTransaction->created_at, 'M d, H:i') }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fs-13 fw-600 font-mono @if($recentTransaction->trx_type == '+') text--success @else text--danger @endif">
                                        {{ $recentTransaction->trx_type }}{{ showAmount($recentTransaction->amount) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-4">
                                <i class="las la-receipt fs-36 text--muted mb-2"></i>
                                <p class="fs-12 text--muted mb-0">@lang('No recent transactions found')</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Offcanvas Drawers -->
    <x-flexible-view :view="$activeTemplate . 'user.components.canvas.deposit'" :meta="['gateways' => $gateways]" />
    <x-flexible-view :view="$activeTemplate . 'user.components.canvas.withdraw'" :meta="['withdrawMethods' => $withdrawMethods]" />

    <!-- KYC Rejection Reason Modal -->
    @if ($user->kv == Status::KYC_UNVERIFIED && $user->kyc_rejection_reason)
        <div class="modal fade custom--modal" id="kycRejectionReason" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text--danger">{{ auth()->user()->kyc_rejection_reason }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                        <a href="{{ route('user.kyc.form') }}" class="btn btn--base btn--sm">@lang('Submit Again')</a>
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
        // Toggle balance visibility (eye icon)
        let isBalanceHidden = localStorage.getItem('vn_balance_hidden') === 'true';
        const actualBalanceHtml = '{{ showAmount($estimatedBalance) }} <span class="fs-18 fw-500 text--muted">USD</span>';
        const hiddenBalanceHtml = '•••••••• <span class="fs-18 fw-500 text--muted">USD</span>';

        function updateBalanceDisplay() {
            if (isBalanceHidden) {
                $('#userBalanceDisplay').html(hiddenBalanceHtml);
                $('#eyeIcon').removeClass('la-eye').addClass('la-eye-slash');
            } else {
                $('#userBalanceDisplay').html(actualBalanceHtml);
                $('#eyeIcon').removeClass('la-eye-slash').addClass('la-eye');
            }
        }

        $('#toggleBalanceBtn').on('click', function() {
            isBalanceHidden = !isBalanceHidden;
            localStorage.setItem('vn_balance_hidden', isBalanceHidden);
            updateBalanceDisplay();
        });

        updateBalanceDisplay();

        // 2FA Notice Close
        $('.2fa-notice .delete-icon').on('click', function() {
            $(this).closest('.2fa-notice').fadeOut(300, function() {
                $(this).remove();
            });
        });

        // Initialize Select2
        function initSelect2() {
            $('.currency_list, #withdraw_currency_list').select2({
                dropdownParent: $('#currency_list_wrapper, #withdraw_currency_list_wrapper'),
                minimumResultsForSearch: 5,
                templateResult: function(currency) {
                    if (!currency.id) return currency.text;
                    return $(
                        '<span class="select2-currency-option d-flex align-items-center gap-2">' +
                        '<img src="' + $(currency.element).data('image') + '" width="20" height="20" class="rounded-circle" /> ' +
                        currency.text +
                        '</span>'
                    );
                },
                templateSelection: function(currency) {
                    if (!currency.id) return currency.text;
                    return $(
                        '<span class="select2-selected-currency d-flex align-items-center gap-2">' +
                        '<img src="' + $(currency.element).data('image') + '" width="20" height="20" class="rounded-circle" /> ' +
                        currency.text +
                        '</span>'
                    );
                }
            });
        }

        $(document).ready(function() {
            initSelect2();

            // Handle deposit form submission
            $(document).on('submit', '.deposit-form', function(e) {
                e.preventDefault();
                const amount = $(this).find('input[name="amount"]').val();
                const currency = $(this).find('.currency_list').val();

                const depositOffcanvas = new bootstrap.Offcanvas(document.getElementById('deposit-canvas'));
                depositOffcanvas.show();

                if (amount) {
                    $('#deposit-canvas').find('input[name="amount"]').val(amount);
                }
                if (currency) {
                    $('#deposit-canvas').find('.currency_list').val(currency).trigger('change');
                }
            });

            // Handle withdraw form submission
            $(document).on('submit', '.withdraw-form', function(e) {
                e.preventDefault();
                const amount = $(this).find('input[name="amount"]').val();
                const currency = $(this).find('#withdraw_currency_list').val();

                const withdrawOffcanvas = new bootstrap.Offcanvas(document.getElementById('withdraw-canvas'));
                withdrawOffcanvas.show();

                if (amount) {
                    $('#withdraw-canvas').find('input[name="amount"]').val(amount);
                }
                if (currency) {
                    $('#withdraw-canvas').find('.currency_list').val(currency).trigger('change');
                }
            });
        });
    })(jQuery);
</script>
@endpush