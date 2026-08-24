@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header bg--primary d-flex justify-content-between align-items-center">
                <h5 class="text-white mb-0"><i class="las la-robot"></i> @lang('Configured AI Trading Bot Models')</h5>
                <button type="button" class="btn btn-sm btn-outline-light addPlanBtn">
                    <i class="las la-plus"></i> @lang('Add New Bot Strategy')
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Bot Name')</th>
                                <th>@lang('Strategy Type')</th>
                                <th>@lang('Investment Min / Max')</th>
                                <th>@lang('Daily ROI Range')</th>
                                <th>@lang('Win Rate')</th>
                                <th>@lang('Duration')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ __($plan->name) }}</span>
                                        <br><small class="text-muted">{{ __($plan->tagline) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge--dark text-uppercase">{{ $plan->strategy_type }}</span>
                                        <br><span class="badge badge--{{ $plan->risk_level == 'low' ? 'success' : ($plan->risk_level == 'medium' ? 'warning' : 'danger') }}">{{ ucfirst($plan->risk_level) }} Risk</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text--primary">${{ showAmount($plan->min_investment) }}</span> - 
                                        <span class="fw-bold text--primary">${{ showAmount($plan->max_investment) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge--success">{{ $plan->daily_roi_min }}% - {{ $plan->daily_roi_max }}% / day</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text--success"><i class="las la-check-circle"></i> {{ $plan->win_rate }}%</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $plan->trade_duration_days }} @lang('Days')</span>
                                    </td>
                                    <td>
                                        @if($plan->status == 1)
                                            <span class="badge badge--success">@lang('Active')</span>
                                        @else
                                            <span class="badge badge--danger">@lang('Disabled')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline--primary editPlanBtn"
                                                data-id="{{ $plan->id }}"
                                                data-name="{{ $plan->name }}"
                                                data-tagline="{{ $plan->tagline }}"
                                                data-strategy="{{ $plan->strategy_type }}"
                                                data-min="{{ $plan->min_investment }}"
                                                data-max="{{ $plan->max_investment }}"
                                                data-roi_min="{{ $plan->daily_roi_min }}"
                                                data-roi_max="{{ $plan->daily_roi_max }}"
                                                data-win_rate="{{ $plan->win_rate }}"
                                                data-risk="{{ $plan->risk_level }}"
                                                data-duration="{{ $plan->trade_duration_days }}"
                                                data-rank="{{ $plan->rank }}"
                                                data-features="{{ json_encode($plan->features) }}"
                                                data-pairs="{{ json_encode($plan->trading_pairs) }}">
                                                <i class="las la-pencil-alt"></i> @lang('Edit')
                                            </button>
                                            
                                            <form action="{{ route('admin.ai.plans.status', $plan->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--{{ $plan->status ? 'warning' : 'success' }}">
                                                    <i class="las la-eye{{ $plan->status ? '-slash' : '' }}"></i> {{ $plan->status ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('admin.ai.plans.delete', $plan->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger confirmationBtn" data-question="@lang('Are you sure you want to delete this bot strategy?')">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">@lang('No AI Bot strategies created yet')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Strategy Modal -->
<div id="planModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg--primary">
                <h5 class="modal-title text-white" id="planModalTitle">@lang('Add AI Bot Strategy')</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.ai.plans.save') }}" method="POST" id="planForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Bot Name')</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Vinance DeepQuant V4.2" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Tagline / Subtitle')</label>
                                <input type="text" name="tagline" class="form-control" placeholder="e.g. High-Frequency Neural Scalper">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Strategy Algorithm')</label>
                                <select name="strategy_type" class="form-control" required>
                                    <option value="scalping">@lang('High-Frequency Scalping')</option>
                                    <option value="breakout">@lang('Volatility Breakout')</option>
                                    <option value="arbitrage">@lang('Cross-Exchange Arbitrage')</option>
                                    <option value="grid">@lang('Smart Geometric Grid')</option>
                                    <option value="trend">@lang('Multi-Timeframe Trend Following')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Risk Level')</label>
                                <select name="risk_level" class="form-control" required>
                                    <option value="low">@lang('Low Risk (Conservative)')</option>
                                    <option value="medium">@lang('Medium Risk (Balanced)')</option>
                                    <option value="high">@lang('High Risk (Aggressive)')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Duration (Days)')</label>
                                <input type="number" name="trade_duration_days" class="form-control" value="30" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Minimum Capital Required ($)')</label>
                                <input type="number" step="any" name="min_investment" class="form-control" placeholder="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Maximum Capital Allocation ($)')</label>
                                <input type="number" step="any" name="max_investment" class="form-control" placeholder="5000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Min Daily ROI (%)')</label>
                                <input type="number" step="any" name="daily_roi_min" class="form-control" placeholder="1.50" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Max Daily ROI (%)')</label>
                                <input type="number" step="any" name="daily_roi_max" class="form-control" placeholder="3.20" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Win Rate Display (%)')</label>
                                <input type="number" step="any" name="win_rate" class="form-control" placeholder="96.5" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Display Rank / Order')</label>
                                <input type="number" name="rank" class="form-control" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save Strategy Plan')</button>
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

        $('.addPlanBtn').on('click', function () {
            var modal = $('#planModal');
            modal.find('#planModalTitle').text("@lang('Add AI Bot Strategy')");
            modal.find('form').attr('action', "{{ route('admin.ai.plans.save') }}");
            modal.find('form')[0].reset();
            modal.modal('show');
        });

        $('.editPlanBtn').on('click', function () {
            var modal = $('#planModal');
            var id = $(this).data('id');
            modal.find('#planModalTitle').text("@lang('Edit AI Bot Strategy')");
            modal.find('form').attr('action', "{{ route('admin.ai.plans.save') }}/" + id);
            
            modal.find('input[name=name]').val($(this).data('name'));
            modal.find('input[name=tagline]').val($(this).data('tagline'));
            modal.find('select[name=strategy_type]').val($(this).data('strategy'));
            modal.find('select[name=risk_level]').val($(this).data('risk'));
            modal.find('input[name=trade_duration_days]').val($(this).data('duration'));
            modal.find('input[name=min_investment]').val($(this).data('min'));
            modal.find('input[name=max_investment]').val($(this).data('max'));
            modal.find('input[name=daily_roi_min]').val($(this).data('roi_min'));
            modal.find('input[name=daily_roi_max]').val($(this).data('roi_max'));
            modal.find('input[name=win_rate]').val($(this).data('win_rate'));
            modal.find('input[name=rank]').val($(this).data('rank'));
            
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
