@extends($activeTemplate . 'layouts.frontend')

@section('content')
<div class="coin-swap-section">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card custom--card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="swap-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="17 1 21 5 17 9"></polyline>
                                <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                                <polyline points="7 23 3 19 7 15"></polyline>
                                <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="card-title mb-1">@lang('Crypto Swap')</h3>
                            <p class="card-subtitle">@lang('Exchange between cryptocurrencies instantly')</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.coin.swap') }}" method="POST" class="coin-swap-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('From Currency')</label>
                                    <select name="from_currency" class="form-select form--control" required>
                                        <option value="">@lang('Select Currency')</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" data-symbol="{{ $currency->symbol }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('To Currency')</label>
                                    <select name="to_currency" class="form-select form--control" required>
                                        <option value="">@lang('Select Currency')</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" data-symbol="{{ $currency->symbol }}">{{ $currency->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label class="form-label">@lang('Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="amount" class="form-control form--control" required>
                                <span class="input-group-text from-symbol">-</span>
                            </div>
                        </div>
                        <div class="swap-details mt-4 p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="detail-item">
                <span class="detail-label">@lang('Rate'):</span>
                <span class="detail-value rate">0</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="detail-item">
                <span class="detail-label">@lang('Transaction Fee') ({{ gs('swap_charge') }}%):</span>
                <span class="detail-value charge">$0</span>
            </div>
        </div>
        <div class="col-12">
            <div class="detail-item total">
                <span class="detail-label">@lang('You Receive'):</span>
                <span class="detail-value">
                    <span class="final-amount">0</span> 
                    <span class="to-symbol">-</span>
                </span>
            </div>
        </div>
    </div>
</div>
                            </div>
                        </div>
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn--primary w-100 py-3">
                                <i class="las la-exchange-alt me-2"></i> @lang('Swap Now')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="card custom--card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="history-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3 class="card-title mb-1">@lang('Swap History')</h3>
                            <p class="card-subtitle">@lang('Your recent exchange transactions')</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table--responsive--lg">
                            <thead>
                                <tr>
                                    <th>@lang('From')</th>
                                    <th>@lang('To')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Rate')</th>
                                    <th>@lang('Fee')</th>
                                    <th>@lang('Date')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($swaps as $swap)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="currency-badge">{{ $swap->fromCurrency->symbol }}</span>
                                                <span>{{ showAmount($swap->from_amount) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="currency-badge">{{ $swap->toCurrency->symbol }}</span>
                                                <span>{{ showAmount($swap->to_amount) }}</span>
                                            </div>
                                        </td>
                                        <td>{{ showAmount($swap->from_amount) }}</td>
                                        <td>{{ showAmount($swap->rate) }}</td>
                                        <td>{{ showAmount($swap->charge) }}</td>
                                        <td>{{ showDateTime($swap->created_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center py-4" colspan="6">
                                            <div class="empty-state">
                                                <i class="las la-exchange-alt"></i>
                                                <p class="mt-2">@lang('No swap history found')</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $swaps->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    /* Base Variables */
    :root {
        /* Light Mode Defaults */
        --body-bg: #f8f9fa;
        --card-bg: #ffffff;
        --card-header-bg: #ffffff;
        --border-color: #e0e0e0;
        --border-radius: 12px;
        --shadow-color: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
        --heading-color: #2c3e50;
        --text-color: #495057;
        --text-muted: #6c757d;
        --input-bg: #ffffff;
        --input-border: #ced4da;
        --swap-details-bg: #f8f9fa;
        --primary-color: #4361ee;
        --primary-light: rgba(67, 97, 238, 0.1);
        --primary-shadow: rgba(67, 97, 238, 0.25);
        --table-header-bg: #f1f3f9;
        --table-row-hover: rgba(67, 97, 238, 0.03);
        --currency-badge-bg: rgba(67, 97, 238, 0.1);
        --currency-badge-color: #4361ee;
    }

    /* Dark Mode Overrides */
    [data-theme="dark"] {
        --body-bg: #121212;
        --card-bg: #1e1e1e;
        --card-header-bg: #252525;
        --border-color: #333333;
        --shadow-color: 0 4px 12px rgba(0, 0, 0, 0.3);
        --shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.4);
        --heading-color: #ffffff;
        --text-color: #e0e0e0;
        --text-muted: #9e9e9e;
        --input-bg: #2d2d2d;
        --input-border: #444444;
        --swap-details-bg: #252525;
        --primary-color: #4cc9f0;
        --primary-light: rgba(76, 201, 240, 0.1);
        --primary-shadow: rgba(76, 201, 240, 0.25);
        --table-header-bg: #2d2d2d;
        --table-row-hover: rgba(76, 201, 240, 0.05);
        --currency-badge-bg: rgba(76, 201, 240, 0.1);
        --currency-badge-color: #4cc9f0;
    }

    /* Base Styles */
    .coin-swap-section {
        min-height: 100vh;
        padding: 2rem 0;
        background: var(--body-bg);
        transition: background 0.3s ease;
    }

    /* Card Styles */
    .custom--card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-color);
        transition: all 0.3s ease;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .custom--card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    /* Card Header */
    .card-header {
        background: var(--card-header-bg);
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .card-title {
        font-weight: 700;
        color: var(--heading-color);
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }

    .card-subtitle {
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    /* Icons */
    .swap-icon svg, 
    .history-icon svg {
        color: var(--primary-color);
        width: 40px;
        height: 40px;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-color);
        font-weight: 500;
    }

    .form--control,
    .form-select {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--text-color);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        width: 100%;
        transition: all 0.3s ease;
    }

    .form--control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem var(--primary-shadow);
        outline: none;
    }

    .input-group-text {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--text-color);
        padding: 0.75rem 1rem;
    }

    /* Swap Details */
    .swap-details {
        background: var(--swap-details-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
    }

    .detail-item.total {
        border-top: 1px solid var(--border-color);
        margin-top: 0.5rem;
        padding-top: 1rem;
    }

    .detail-label {
        color: var(--text-muted);
        font-weight: 500;
    }

    .detail-value {
        color: var(--text-color);
        font-weight: 600;
    }

    .detail-item.total .detail-value {
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    /* Button Styles */
    .btn--primary {
        background: var(--primary-color);
        border: none;
        color: white;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px var(--primary-shadow);
    }

    .btn--primary:hover {
        background: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px var(--primary-shadow);
    }

    /* Table Styles */
    .table {
        color: var(--text-color);
        margin-bottom: 0;
        border-radius: 10px;
    }

    .table thead th {
        background: var(--table-header-bg);
        border-color: var(--border-color);
        font-weight: 600;
        padding: 1rem;
    }

    .table td {
        border-color: var(--border-color);
        padding: 1rem;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: var(--table-row-hover);
        border-radius: 10px;
    }

    /* Currency Badge */
    .currency-badge {
        background: var(--currency-badge-bg);
        color: var(--currency-badge-color);
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    /* Empty State */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: var(--text-muted);
        padding: 2rem 0;
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 767px) {
        .card-header {
            padding: 1.25rem;
        }
        
        .card-title {
            font-size: 1.25rem;
        }
        
        .swap-icon svg,
        .history-icon svg {
            width: 32px;
            height: 32px;
        }
        
        .form--control,
        .form-select,
        .input-group-text {
            padding: 0.625rem 0.875rem;
        }
    }
</style>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";
        
        // Currency swap calculation
        $('select[name=from_currency], select[name=to_currency], input[name=amount]').on('change input', function() {
    let fromCurrency = $('select[name=from_currency]').val();
    let toCurrency = $('select[name=to_currency]').val();
    let amount = $('input[name=amount]').val();
    
    if(fromCurrency && toCurrency && amount > 0) {
        $.ajax({
            url: "{{ route('user.coin.swap.calculate') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                from_currency: fromCurrency,
                to_currency: toCurrency,
                amount: amount
            },
            success: function(response) {
                if(response.success) {
                    $('.rate').text(response.rate_display); // Shows "4.3669 POL/USDT"
                    $('.charge').text(response.charge); // Shows "$0.8734"
                    $('.final-amount').text(response.final_amount); // Shows "86.4636"
                    $('.to-symbol').text(response.to_symbol); // Shows "POL"
                }
            }
        });
    }
});

        // Form submission
        $('.coin-swap-form').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('.btn--primary').prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Processing...');
                },
                success: function(response) {
                    if(response.success) {
                        notify('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        notify('error', response.error || 'Something went wrong');
                        $('.btn--primary').prop('disabled', false).html('<i class="las la-exchange-alt me-2"></i> @lang("Swap Now")');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Something went wrong';
                    if(xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    notify('error', errorMessage);
                    $('.btn--primary').prop('disabled', false).html('<i class="las la-exchange-alt me-2"></i> @lang("Swap Now")');
                }
            });
        });
        
        // Theme switcher support
        $(document).on('themeChanged', function(event, theme) {
            document.documentElement.setAttribute('data-theme', theme);
        });
    })(jQuery);
</script>
@endpush

@endsection