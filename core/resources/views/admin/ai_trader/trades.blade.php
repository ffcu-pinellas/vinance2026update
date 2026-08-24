@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header bg--primary d-flex justify-content-between align-items-center">
                <h5 class="text-white mb-0"><i class="las la-chart-bar"></i> @lang('AI Trade Execution Logs & Manual Injector')</h5>
                <button type="button" class="btn btn-sm btn-outline-light injectTradeBtn">
                    <i class="las la-plus-circle"></i> @lang('Inject Custom Bot Trade')
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Pair')</th>
                                <th>@lang('Side')</th>
                                <th>@lang('Entry Price')</th>
                                <th>@lang('Exit Price')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Realized Profit')</th>
                                <th>@lang('Date / Time')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trades as $trade)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.users.detail', $trade->user_id) }}" class="fw-bold">{{ @$trade->user->username }}</a>
                                    </td>
                                    <td><span class="fw-bold">{{ $trade->pair_symbol }}</span></td>
                                    <td>
                                        <span class="badge badge--{{ $trade->side == 'BUY' ? 'success' : 'danger' }}">{{ $trade->side }}</span>
                                    </td>
                                    <td>${{ showAmount($trade->entry_price) }}</td>
                                    <td>${{ showAmount($trade->exit_price) }}</td>
                                    <td>${{ showAmount($trade->amount) }}</td>
                                    <td>
                                        <span class="text--success fw-bold">+${{ showAmount($trade->profit_amount) }}</span>
                                        <br><small class="text-muted">(+{{ $trade->profit_percentage }}%)</small>
                                    </td>
                                    <td>{{ showDateTime($trade->created_at) }}</td>
                                    <td>
                                        <form action="{{ route('admin.ai.trades.delete', $trade->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline--danger confirmationBtn" data-question="@lang('Delete this trade record?')">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">@lang('No AI trade logs recorded yet')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($trades->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($trades) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Inject Trade Modal -->
<div id="injectModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary">
                <h5 class="modal-title text-white">@lang('Inject Custom AI Bot Trade for User')</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.ai.trades.inject') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Select User')</label>
                                <select name="user_id" class="form-control select2" required>
                                    <option value="" selected disabled>@lang('Select User')</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->username }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Trading Pair')</label>
                                <input type="text" name="pair_symbol" class="form-control" value="BTC/USDT" placeholder="e.g. BTC/USDT" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Order Side')</label>
                                <select name="side" class="form-control" required>
                                    <option value="BUY">BUY (Long)</option>
                                    <option value="SELL">SELL (Short)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Entry Price ($)')</label>
                                <input type="number" step="any" name="entry_price" class="form-control" placeholder="64500.00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Exit Price ($)')</label>
                                <input type="number" step="any" name="exit_price" class="form-control" placeholder="65800.00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Trade Volume Amount ($)')</label>
                                <input type="number" step="any" name="amount" class="form-control" placeholder="500.00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Profit Amount ($)')</label>
                                <input type="number" step="any" name="profit_amount" class="form-control" placeholder="18.50" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Profit Percentage (%)')</label>
                                <input type="number" step="any" name="profit_percentage" class="form-control" placeholder="3.70" required>
                            </div>
                        </div>
                        <input type="hidden" name="status" value="closed">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45"><i class="las la-bolt"></i> @lang('Inject & Credit Bot Trade')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('script')
<script>
    (function ($) {
        "use strict";
        $('.injectTradeBtn').on('click', function () {
            $('#injectModal').modal('show');
        });
    })(jQuery);
</script>
@endpush
