@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <!-- User Overrides Form -->
    <div class="col-lg-5">
        <div class="card b-radius--10 border--primary">
            <div class="card-header bg--primary">
                <h5 class="text-white mb-0"><i class="las la-user-cog"></i> @lang('AI Trader Overrides for') {{ $user->username }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.ai.settings.update', $user->id) }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label>@lang('Bot Operation Mode')</label>
                        <select name="force_status" class="form-control">
                            <option value="" {{ @$setting->force_status === null ? 'selected' : '' }}>@lang('Default (User Controlled)')</option>
                            <option value="1" {{ @$setting->force_status === 1 ? 'selected' : '' }}>@lang('Force Always Active (High Priority)')</option>
                            <option value="0" {{ @$setting->force_status === 0 ? 'selected' : '' }}>@lang('Force Deactivated / Suspended')</option>
                        </select>
                        <small class="text-muted">@lang('Override bot activity state regardless of user balance or toggle.')</small>
                    </div>

                    <div class="form-group">
                        <label>@lang('Custom Win Rate Override (%)')</label>
                        <div class="input-group">
                            <input type="number" step="any" name="custom_win_rate" class="form-control" value="{{ old('custom_win_rate', @$setting->custom_win_rate) }}" placeholder="e.g. 97.5">
                            <div class="input-group-text">%</div>
                        </div>
                        <small class="text-muted">@lang('Leave empty to use strategy default.')</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Min Daily ROI (%)')</label>
                                <div class="input-group">
                                    <input type="number" step="any" name="custom_daily_roi_min" class="form-control" value="{{ old('custom_daily_roi_min', @$setting->custom_daily_roi_min) }}" placeholder="e.g. 2.5">
                                    <div class="input-group-text">%</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Max Daily ROI (%)')</label>
                                <div class="input-group">
                                    <input type="number" step="any" name="custom_daily_roi_max" class="form-control" value="{{ old('custom_daily_roi_max', @$setting->custom_daily_roi_max) }}" placeholder="e.g. 5.0">
                                    <div class="input-group-text">%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>@lang('Admin Internal Notes')</label>
                        <textarea name="custom_notes" class="form-control" rows="3" placeholder="Special requirements, VIP parameters...">{{ old('custom_notes', @$setting->custom_notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn--primary w-100 h-45 mt-3"><i class="las la-save"></i> @lang('Save User AI Overrides')</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Active Bots & Performance for this User -->
    <div class="col-lg-7">
        <div class="card b-radius--10 mb-4">
            <div class="card-header bg--primary">
                <h5 class="text-white mb-0"><i class="las la-robot"></i> @lang('Deployed Bots by') {{ $user->username }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Bot Strategy')</th>
                                <th>@lang('Allocated')</th>
                                <th>@lang('Current Profit')</th>
                                <th>@lang('Trades')</th>
                                <th>@lang('Status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userBots as $bot)
                                <tr>
                                    <td><span class="fw-bold">{{ @$bot->plan->name ?? 'Custom Bot' }}</span></td>
                                    <td><span class="fw-bold text--primary">${{ showAmount($bot->allocated_amount) }}</span></td>
                                    <td><span class="fw-bold text--success">+${{ showAmount($bot->current_profit) }}</span></td>
                                    <td>{{ $bot->total_trades }}</td>
                                    <td>
                                        @if($bot->status == 1)
                                            <span class="badge badge--success">@lang('Running')</span>
                                        @else
                                            <span class="badge badge--warning">@lang('Paused')</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">@lang('No bots active for this user')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Trade Executions -->
        <div class="card b-radius--10">
            <div class="card-header bg--primary">
                <h5 class="text-white mb-0"><i class="las la-history"></i> @lang('Recent Bot Trade Executions')</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Pair')</th>
                                <th>@lang('Side')</th>
                                <th>@lang('Entry / Exit')</th>
                                <th>@lang('Profit')</th>
                                <th>@lang('Time')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trades as $t)
                                <tr>
                                    <td><span class="fw-bold">{{ $t->pair_symbol }}</span></td>
                                    <td><span class="badge badge--{{ $t->side == 'BUY' ? 'success' : 'danger' }}">{{ $t->side }}</span></td>
                                    <td>${{ showAmount($t->entry_price) }} &rarr; ${{ showAmount($t->exit_price) }}</td>
                                    <td>
                                        <span class="text--success fw-bold">+${{ showAmount($t->profit_amount) }}</span>
                                        <small class="text-muted">(+{{ $t->profit_percentage }}%)</small>
                                    </td>
                                    <td>{{ showDateTime($t->created_at, 'M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">@lang('No trade logs for this user')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn-sm btn-outline--primary"><i class="las la-undo"></i> @lang('Back to User Detail')</a>
@endpush
