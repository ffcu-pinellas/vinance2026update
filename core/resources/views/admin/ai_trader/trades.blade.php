@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header bg--primary d-flex justify-content-between align-items-center">
                <h5 class="text-white mb-0"><i class="las la-chart-bar"></i> @lang('AI Trade Execution Logs & Advanced Injector')</h5>
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
                                    <td>${{ showAmount($trade->entry_price, currencyFormat: false) }}</td>
                                    <td>${{ showAmount($trade->exit_price, currencyFormat: false) }}</td>
                                    <td>${{ showAmount($trade->amount, currencyFormat: false) }}</td>
                                    <td>
                                        <span class="{{ $trade->profit_amount >= 0 ? 'text--success' : 'text--danger' }} fw-bold">
                                            {{ $trade->profit_amount >= 0 ? '+' : '' }}${{ showAmount($trade->profit_amount, currencyFormat: false) }}
                                        </span>
                                        <br><small class="text-muted">({{ $trade->profit_percentage >= 0 ? '+' : '' }}{{ $trade->profit_percentage }}%)</small>
                                    </td>
                                    <td>{{ showDateTime($trade->created_at) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline--primary editTradeBtn"
                                                data-id="{{ $trade->id }}"
                                                data-user="{{ @$trade->user->username }}"
                                                data-pair="{{ $trade->pair_symbol }}"
                                                data-side="{{ $trade->side }}"
                                                data-entry="{{ $trade->entry_price }}"
                                                data-exit="{{ $trade->exit_price }}"
                                                data-amount="{{ $trade->amount }}"
                                                data-profit_amount="{{ $trade->profit_amount }}"
                                                data-profit_pct="{{ $trade->profit_percentage }}"
                                                data-status="{{ $trade->status }}"
                                                data-created_at="{{ $trade->created_at ? $trade->created_at->format('Y-m-d\TH:i') : '' }}">
                                                <i class="las la-pencil-alt"></i> @lang('Edit')
                                            </button>

                                            <form action="{{ route('admin.ai.trades.delete', $trade->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger confirmationBtn" data-question="@lang('Delete this trade record?')">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
                <h5 class="modal-title text-white">@lang('Inject AI Bot Trade with Auto Calculator')</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.ai.trades.inject') }}" method="POST" id="injectForm">
                @csrf
                <div class="modal-body">
                    <!-- Helper Banner -->
                    <div class="alert alert-primary d-flex align-items-center mb-3">
                        <i class="las la-calculator fs-3 me-2"></i>
                        <div>
                            <strong>@lang('Auto-Correct Math Calculator Enabled'):</strong> 
                            @lang('Enter Entry Price, Exit Price, and Volume to automatically calculate Profit Amount ($) & Profit (%).')
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Select User')</label>
                                <select name="user_id" id="injectUserSelect" class="form-control select2" required>
                                    <option value="" selected disabled>@lang('Select User')</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->username }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Target Deployed Bot (Optional)')</label>
                                <select name="user_ai_bot_id" id="injectBotSelect" class="form-control">
                                    <option value="">@lang('Auto-Detect Active Bot / Default')</option>
                                    @foreach($userBots as $ub)
                                        <option value="{{ $ub->id }}" data-user="{{ $ub->user_id }}">
                                            {{ @$ub->user->username }} - {{ @$ub->plan->name }} (${{ showAmount($ub->allocated_amount, currencyFormat: false) }})
                                        </option>
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

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Order Side')</label>
                                <select name="side" id="injectSideSelect" class="form-control" required>
                                    <option value="BUY">BUY (Long)</option>
                                    <option value="SELL">SELL (Short)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Entry Price ($)')</label>
                                <input type="number" step="any" name="entry_price" id="injectEntryPrice" class="form-control" placeholder="64250.00" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Exit Price ($)')</label>
                                <input type="number" step="any" name="exit_price" id="injectExitPrice" class="form-control" placeholder="65480.00" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Trade Volume Amount ($)')</label>
                                <input type="number" step="any" name="amount" id="injectVolumeAmount" class="form-control" placeholder="500.00" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Profit / Loss Amount ($)') <span class="badge badge--success" id="calcBadgeInject">PROFIT</span></label>
                                <input type="number" step="any" name="profit_amount" id="injectProfitAmount" class="form-control" placeholder="18.50" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Profit / Loss Percentage (%)')</label>
                                <div class="input-group">
                                    <input type="number" step="any" name="profit_percentage" id="injectProfitPct" class="form-control" placeholder="3.70" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Trade Status')</label>
                                <select name="status" class="form-control">
                                    <option value="closed" selected>Closed (Completed)</option>
                                    <option value="open">Open (In Progress)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Execution Date / Time')</label>
                                <input type="datetime-local" name="created_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45"><i class="las la-bolt"></i> @lang('Inject & Credit Bot Trade')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Trade Modal -->
<div id="editTradeModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary">
                <h5 class="modal-title text-white">@lang('Edit AI Bot Trade Record')</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="" method="POST" id="editTradeForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('User')</label>
                                <input type="text" id="editUsername" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Trading Pair')</label>
                                <input type="text" name="pair_symbol" id="editPair" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Order Side')</label>
                                <select name="side" id="editSide" class="form-control" required>
                                    <option value="BUY">BUY (Long)</option>
                                    <option value="SELL">SELL (Short)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Entry Price ($)')</label>
                                <input type="number" step="any" name="entry_price" id="editEntryPrice" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Exit Price ($)')</label>
                                <input type="number" step="any" name="exit_price" id="editExitPrice" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Trade Volume Amount ($)')</label>
                                <input type="number" step="any" name="amount" id="editVolumeAmount" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Profit / Loss Amount ($)')</label>
                                <input type="number" step="any" name="profit_amount" id="editProfitAmount" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Profit / Loss Percentage (%)')</label>
                                <div class="input-group">
                                    <input type="number" step="any" name="profit_percentage" id="editProfitPct" class="form-control" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Trade Status')</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="closed">Closed (Completed)</option>
                                    <option value="open">Open (In Progress)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Execution Date / Time')</label>
                                <input type="datetime-local" name="created_at" id="editCreatedAt" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45"><i class="las la-save"></i> @lang('Save Changes')</button>
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

        // Open Inject Modal
        $('.injectTradeBtn').on('click', function () {
            $('#injectModal').modal('show');
        });

        // Filter bot selector when user changes
        $('#injectUserSelect').on('change', function() {
            var userId = $(this).val();
            $('#injectBotSelect option').each(function() {
                var botUser = $(this).data('user');
                if (!botUser || botUser == userId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#injectBotSelect').val('');
        });

        // Open Edit Modal
        $('.editTradeBtn').on('click', function () {
            var modal = $('#editTradeModal');
            var id = $(this).data('id');
            modal.find('#editTradeForm').attr('action', "{{ route('admin.ai.trades.update', '') }}/" + id);

            modal.find('#editUsername').val($(this).data('user'));
            modal.find('#editPair').val($(this).data('pair'));
            modal.find('#editSide').val($(this).data('side'));
            modal.find('#editEntryPrice').val($(this).data('entry'));
            modal.find('#editExitPrice').val($(this).data('exit'));
            modal.find('#editVolumeAmount').val($(this).data('amount'));
            modal.find('#editProfitAmount').val($(this).data('profit_amount'));
            modal.find('#editProfitPct').val($(this).data('profit_pct'));
            modal.find('#editStatus').val($(this).data('status'));
            modal.find('#editCreatedAt').val($(this).data('created_at'));

            modal.modal('show');
        });

        // Smart Math Auto-Calculator Helper Function
        function calculateTradeMath(prefix) {
            var entry = parseFloat($('#' + prefix + 'EntryPrice').val()) || 0;
            var exit = parseFloat($('#' + prefix + 'ExitPrice').val()) || 0;
            var volume = parseFloat($('#' + prefix + 'VolumeAmount').val()) || 0;
            var side = $('#' + prefix + 'SideSelect').val() || $('#' + prefix + 'Side').val() || 'BUY';

            if (entry > 0 && exit > 0 && volume > 0) {
                var pct = 0;
                if (side === 'BUY') {
                    pct = ((exit - entry) / entry) * 100;
                } else {
                    pct = ((entry - exit) / entry) * 100;
                }
                var profitAmount = volume * (pct / 100);

                $('#' + prefix + 'ProfitPct').val(pct.toFixed(2));
                $('#' + prefix + 'ProfitAmount').val(profitAmount.toFixed(2));

                if (prefix === 'inject') {
                    if (profitAmount >= 0) {
                        $('#calcBadgeInject').removeClass('badge--danger').addClass('badge--success').text('PROFIT (+' + pct.toFixed(2) + '%)');
                    } else {
                        $('#calcBadgeInject').removeClass('badge--success').addClass('badge--danger').text('LOSS (' + pct.toFixed(2) + '%)');
                    }
                }
            }
        }

        // Bind calculator events for Inject Modal
        $('#injectEntryPrice, #injectExitPrice, #injectVolumeAmount, #injectSideSelect').on('input change', function() {
            calculateTradeMath('inject');
        });

        // If admin types Profit Amount directly -> auto-calculate Profit %
        $('#injectProfitAmount').on('input', function() {
            var profit = parseFloat($(this).val()) || 0;
            var volume = parseFloat($('#injectVolumeAmount').val()) || 0;
            if (volume > 0) {
                var pct = (profit / volume) * 100;
                $('#injectProfitPct').val(pct.toFixed(2));
            }
        });

        // Bind calculator events for Edit Modal
        $('#editEntryPrice, #editExitPrice, #editVolumeAmount, #editSide').on('input change', function() {
            calculateTradeMath('edit');
        });

        $('#editProfitAmount').on('input', function() {
            var profit = parseFloat($(this).val()) || 0;
            var volume = parseFloat($('#editVolumeAmount').val()) || 0;
            if (volume > 0) {
                var pct = (profit / volume) * 100;
                $('#editProfitPct').val(pct.toFixed(2));
            }
        });
    })(jQuery);
</script>
@endpush
