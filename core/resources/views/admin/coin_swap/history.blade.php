@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">@lang('All Swap Transactions & Manual Injector')</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn--success injectSwapBtn">
                        <i class="las la-plus-circle"></i> @lang('Inject / Create Swap')
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Sold (From)')</th>
                                <th>@lang('Received (To)')</th>
                                <th>@lang('Execution Rate')</th>
                                <th>@lang('Fee')</th>
                                <th>@lang('Date & Time')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($swaps as $swap)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ @$swap->user->fullname }}</span>
                                        <br>
                                        <span class="small">
                                            <a href="{{ route('admin.users.detail', @$swap->user_id) }}"><span>@</span>{{ @$swap->user->username }}</a>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text--danger fw-bold">-{{ number_format($swap->from_amount, 6) }}</span>
                                        <span class="badge badge--dark ms-1">{{ @$swap->fromCurrency->symbol }}</span>
                                    </td>
                                    <td>
                                        <span class="text--success fw-bold">+{{ number_format($swap->to_amount, 6) }}</span>
                                        <span class="badge badge--dark ms-1">{{ @$swap->toCurrency->symbol }}</span>
                                    </td>
                                    <td>
                                        <span>1 {{ @$swap->fromCurrency->symbol }} ≈ {{ number_format($swap->rate, 6) }} {{ @$swap->toCurrency->symbol }}</span>
                                    </td>
                                    <td>
                                        <span>{{ number_format($swap->charge, 6) }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $swap->created_at->format('Y-m-d H:i') }}</span>
                                    </td>
                                    <td>
                                        @if($swap->status == 1)
                                            <span class="badge badge--success">@lang('Completed')</span>
                                        @else
                                            <span class="badge badge--danger">@lang('Reverted')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="button--group">
                                            <button type="button" class="btn btn-sm btn--outline-primary editSwapBtn"
                                                data-swap="{{ $swap }}"
                                                data-url="{{ route('admin.swap.update', $swap->id) }}">
                                                <i class="la la-pencil"></i> @lang('Edit')
                                            </button>
                                            @if($swap->status == 1)
                                                <button type="button" class="btn btn-sm btn--outline-warning confirmationBtn"
                                                    data-action="{{ route('admin.swap.revert', $swap->id) }}"
                                                    data-question="@lang('Revert this swap? (Refunds sold asset to user and deducts received asset)')">
                                                    <i class="la la-undo"></i> @lang('Revert')
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn--outline-danger confirmationBtn"
                                                data-action="{{ route('admin.swap.delete', $swap->id) }}"
                                                data-question="@lang('Are you sure you want to delete this swap record?')">
                                                <i class="la la-trash"></i> @lang('Delete')
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">@lang('No swap transactions found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($swaps->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($swaps) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Inject Swap Modal -->
<div id="injectSwapModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Manual Swap Injector')</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.swap.inject') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Select User -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Target User')</label>
                            <select name="user_id" class="form-control form-select select2" required>
                                <option value="">-- @lang('Select User') --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->fullname }} - {{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Time -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Execution Timestamp')</label>
                            <input type="datetime-local" name="created_at" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                        </div>

                        <!-- From Currency -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Sold Asset (From)')</label>
                            <select name="from_currency_id" class="form-control form-select" required>
                                <option value="">-- @lang('Select From') --</option>
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr->id }}">{{ $curr->name }} ({{ $curr->symbol }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- To Currency -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Received Asset (To)')</label>
                            <select name="to_currency_id" class="form-control form-select" required>
                                <option value="">-- @lang('Select To') --</option>
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr->id }}">{{ $curr->name }} ({{ $curr->symbol }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- From Amount -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Sold Amount (From Amount)')</label>
                            <input type="number" step="any" name="from_amount" id="injectFromAmount" class="form-control" placeholder="1.0" required>
                        </div>

                        <!-- Rate -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Execution Rate (1 From = X To)')</label>
                            <input type="number" step="any" name="rate" id="injectRate" class="form-control" placeholder="77901.50" required>
                        </div>

                        <!-- To Amount -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Received Amount (To Amount)')</label>
                            <input type="number" step="any" name="to_amount" id="injectToAmount" class="form-control" placeholder="77901.50" required>
                        </div>

                        <!-- Fee / Charge -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Fee / Charge Amount')</label>
                            <input type="number" step="any" name="charge" class="form-control" value="0.00" required>
                        </div>

                        <!-- Adjust Balance Toggle -->
                        <div class="col-12 form-group">
                            <label class="form-label">@lang('Adjust User Wallet Balances?')</label>
                            <select name="adjust_balance" class="form-control form-select">
                                <option value="0" selected>@lang('No (Record Only / Direct Injection)')</option>
                                <option value="1">@lang('Yes (Deduct Sold Coin & Credit Received Coin to Spot Wallet)')</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Confirm & Inject Swap')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Swap Modal -->
<div id="editSwapModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Edit Swap Record')</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="" method="POST" id="editSwapForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">@lang('Sold Amount (From Amount)')</label>
                        <input type="number" step="any" name="from_amount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('Received Amount (To Amount)')</label>
                        <input type="number" step="any" name="to_amount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('Execution Rate')</label>
                        <input type="number" step="any" name="rate" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('Fee / Charge Amount')</label>
                        <input type="number" step="any" name="charge" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('Execution Timestamp')</label>
                        <input type="datetime-local" name="created_at" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save Changes')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn--outline-success injectSwapBtn">
        <i class="las la-plus-circle"></i> @lang('Inject Swap')
    </button>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";

        $('.injectSwapBtn').on('click', function () {
            $('#injectSwapModal').modal('show');
        });

        $('#injectFromAmount, #injectRate').on('input', function() {
            var from = parseFloat($('#injectFromAmount').val()) || 0;
            var rate = parseFloat($('#injectRate').val()) || 0;
            if (from > 0 && rate > 0) {
                $('#injectToAmount').val((from * rate).toFixed(6));
            }
        });

        $('.editSwapBtn').on('click', function () {
            var swap = $(this).data('swap');
            var url = $(this).data('url');
            var modal = $('#editSwapModal');

            modal.find('#editSwapForm').attr('action', url);
            modal.find('input[name=from_amount]').val(swap.from_amount);
            modal.find('input[name=to_amount]').val(swap.to_amount);
            modal.find('input[name=rate]').val(swap.rate);
            modal.find('input[name=charge]').val(swap.charge);

            if (swap.created_at) {
                var dtFormatted = swap.created_at.substring(0, 16);
                modal.find('input[name=created_at]').val(dtFormatted);
            }

            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
