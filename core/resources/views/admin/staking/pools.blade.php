@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">@lang('All Staking Pools & Yield Plans')</h5>
                <button type="button" class="btn btn-sm btn--primary addPoolBtn">
                    <i class="las la-plus"></i> @lang('Add New Pool')
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Pool Name')</th>
                                <th>@lang('Asset')</th>
                                <th>@lang('Type & Duration')</th>
                                <th>@lang('APY Rate')</th>
                                <th>@lang('Min / Max Limits')</th>
                                <th>@lang('Active Stakers')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pools as $pool)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $pool->name }}</span>
                                        @if($pool->badge_tag)
                                            <span class="badge badge--warning ms-1">{{ $pool->badge_tag }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge--dark fw-bold">{{ $pool->token_symbol }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge--{{ $pool->type == 'flexible' ? 'info' : 'warning' }}-soft">
                                            {{ ucfirst($pool->type) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $pool->lock_period_days > 0 ? $pool->lock_period_days . ' Days Lock' : 'Flexible Withdrawal' }}</small>
                                    </td>
                                    <td>
                                        <span class="text--success fw-bold fs-6">{{ $pool->apy_rate }}% APY</span>
                                        <br>
                                        <small class="text-muted">{{ number_format($pool->apy_rate / 365, 4) }}% / day</small>
                                    </td>
                                    <td>
                                        <span>Min: ${{ number_format($pool->min_amount, 0) }}</span>
                                        <br>
                                        <span>Max: ${{ number_format($pool->max_amount, 0) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $pool->stakes_count }} stakes</span>
                                        <br>
                                        <small class="text-muted">${{ number_format($pool->total_staked, 2) }} staked</small>
                                    </td>
                                    <td>
                                        @if($pool->is_active)
                                            <span class="badge badge--success">@lang('Active')</span>
                                        @else
                                            <span class="badge badge--danger">@lang('Inactive')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="button--group">
                                            <button type="button" class="btn btn-sm btn--outline-primary editPoolBtn"
                                                data-pool="{{ $pool }}">
                                                <i class="la la-pencil"></i> @lang('Edit')
                                            </button>
                                            <button type="button" class="btn btn-sm btn--outline-{{ $pool->is_active ? 'warning' : 'success' }} confirmationBtn"
                                                data-action="{{ route('admin.staking.pools.status', $pool->id) }}"
                                                data-question="@lang('Are you sure you want to change this pool status?')">
                                                <i class="la la-eye{{ $pool->is_active ? '-slash' : '' }}"></i>
                                                {{ $pool->is_active ? __('Disable') : __('Enable') }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn--outline-danger confirmationBtn"
                                                data-action="{{ route('admin.staking.pools.delete', $pool->id) }}"
                                                data-question="@lang('Are you sure you want to delete this staking pool?')">
                                                <i class="la la-trash"></i> @lang('Delete')
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">@lang('No staking pools configured yet')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($pools->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($pools) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add / Edit Pool Modal -->
<div id="poolModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">@lang('Add Staking Pool')</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.staking.pools.save') }}" method="POST" id="poolForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Pool Name')</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. USDT 30-Day Locked Vault" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Asset / Token Symbol')</label>
                            <input type="text" name="token_symbol" class="form-control" placeholder="e.g. USDT, BTC, ETH, SOL" value="USDT" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Pool Type')</label>
                            <select name="type" class="form-control form-select" id="poolTypeSelect" required>
                                <option value="locked">Locked Term</option>
                                <option value="flexible">Flexible</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group" id="lockPeriodGroup">
                            <label class="form-label">@lang('Lock Period (Days)')</label>
                            <input type="number" name="lock_period_days" class="form-control" value="30" min="0">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Annual Percentage Yield (APY %)')</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="apy_rate" class="form-control" placeholder="e.g. 18.50" required>
                                <span class="input-group-text">% APY</span>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Early Unstake Penalty (%)')</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="early_unstake_penalty_percentage" class="form-control" placeholder="e.g. 2.50" value="0">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Minimum Stake Amount ($)')</label>
                            <input type="number" step="any" name="min_amount" class="form-control" value="100" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Maximum Stake Amount ($)')</label>
                            <input type="number" step="any" name="max_amount" class="form-control" value="100000" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Badge Tag (Optional)')</label>
                            <input type="text" name="badge_tag" class="form-control" placeholder="e.g. POPULAR, HOT, VIP">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Display Rank Order')</label>
                            <input type="number" name="rank" class="form-control" value="1" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit Pool')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn--outline-primary addPoolBtn">
        <i class="las la-plus"></i> @lang('Add New Pool')
    </button>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";

        var poolModal = $('#poolModal');
        var defaultAction = "{{ route('admin.staking.pools.save') }}";

        $('.addPoolBtn').on('click', function () {
            poolModal.find('#modalTitle').text("@lang('Add Staking Pool')");
            poolModal.find('#poolForm').attr('action', defaultAction);
            poolModal.find('#poolForm')[0].reset();
            $('#lockPeriodGroup').show();
            poolModal.modal('show');
        });

        $('.editPoolBtn').on('click', function () {
            var pool = $(this).data('pool');
            var updateUrl = "{{ route('admin.staking.pools.save', '') }}/" + pool.id;

            poolModal.find('#modalTitle').text("@lang('Edit Staking Pool') - " + pool.name);
            poolModal.find('#poolForm').attr('action', updateUrl);

            poolModal.find('input[name=name]').val(pool.name);
            poolModal.find('input[name=token_symbol]').val(pool.token_symbol);
            poolModal.find('select[name=type]').val(pool.type);
            poolModal.find('input[name=lock_period_days]').val(pool.lock_period_days);
            poolModal.find('input[name=apy_rate]').val(pool.apy_rate);
            poolModal.find('input[name=early_unstake_penalty_percentage]').val(pool.early_unstake_penalty_percentage);
            poolModal.find('input[name=min_amount]').val(pool.min_amount);
            poolModal.find('input[name=max_amount]').val(pool.max_amount);
            poolModal.find('input[name=badge_tag]').val(pool.badge_tag);
            poolModal.find('input[name=rank]').val(pool.rank);

            if (pool.type === 'flexible') {
                $('#lockPeriodGroup').hide();
            } else {
                $('#lockPeriodGroup').show();
            }

            poolModal.modal('show');
        });

        $('#poolTypeSelect').on('change', function () {
            if ($(this).val() === 'flexible') {
                $('#lockPeriodGroup').hide();
            } else {
                $('#lockPeriodGroup').show();
            }
        });
    })(jQuery);
</script>
@endpush