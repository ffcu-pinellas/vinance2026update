@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">@lang('All Staking Positions & Advanced Injector')</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn--success injectStakeBtn">
                        <i class="las la-plus-circle"></i> @lang('Inject / Create Stake')
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Vault Pool')</th>
                                <th>@lang('Principal')</th>
                                <th>@lang('APY Rate')</th>
                                <th>@lang('Accumulated Yield')</th>
                                <th>@lang('Start Date')</th>
                                <th>@lang('Maturity Date')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stakes as $stake)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ @$stake->user->fullname }}</span>
                                        <br>
                                        <span class="small">
                                            <a href="{{ route('admin.users.detail', @$stake->user_id) }}"><span>@</span>{{ @$stake->user->username }}</a>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ @$stake->pool->name ?? 'Staking Vault' }}</span>
                                        <br>
                                        <span class="badge badge--dark">{{ @$stake->pool->token_symbol ?? 'USDT' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">${{ number_format($stake->principal_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge--info">{{ @$stake->pool->apy_rate ?? 12 }}% APY</span>
                                    </td>
                                    <td>
                                        <span class="text--success fw-bold">+${{ number_format($stake->accumulated_rewards, 2) }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $stake->start_time ? $stake->start_time->format('Y-m-d H:i') : 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $stake->end_time ? $stake->end_time->format('Y-m-d H:i') : 'Flexible' }}</span>
                                    </td>
                                    <td>
                                        @if($stake->status == 'active')
                                            <span class="badge badge--success">@lang('Active')</span>
                                        @else
                                            <span class="badge badge--dark">{{ ucfirst($stake->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="button--group">
                                            <button type="button" class="btn btn-sm btn--outline-primary editStakeBtn"
                                                data-stake="{{ $stake }}"
                                                data-url="{{ route('admin.staking.stakes.update', $stake->id) }}">
                                                <i class="la la-pencil"></i> @lang('Edit')
                                            </button>
                                            @if($stake->status == 'active')
                                                <button type="button" class="btn btn-sm btn--outline-warning confirmationBtn"
                                                    data-action="{{ route('admin.staking.stakes.return', $stake->id) }}"
                                                    data-question="@lang('Return this stake principal + yield directly to user\'s Spot Wallet?')">
                                                    <i class="la la-reply"></i> @lang('Refund')
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn--outline-danger confirmationBtn"
                                                data-action="{{ route('admin.staking.stakes.delete', $stake->id) }}"
                                                data-question="@lang('Are you sure you want to delete this stake record?')">
                                                <i class="la la-trash"></i> @lang('Delete')
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">@lang('No stake positions found')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($stakes->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($stakes) }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Inject Stake Modal -->
<div id="injectStakeModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Manual Stake Injector')</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.staking.stakes.inject') }}" method="POST">
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

                        <!-- Select Staking Pool -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Staking Vault Pool')</label>
                            <select name="pool_id" class="form-control form-select" id="injectPoolSelect" required>
                                <option value="">-- @lang('Select Pool') --</option>
                                @foreach($pools as $pool)
                                    <option value="{{ $pool->id }}" data-apy="{{ $pool->apy_rate }}" data-days="{{ $pool->lock_period_days }}">
                                        {{ $pool->name }} ({{ $pool->apy_rate }}% APY - {{ $pool->lock_period_days > 0 ? $pool->lock_period_days . 'D' : 'Flex' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Principal Amount -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Principal Amount ($)')</label>
                            <input type="number" step="any" name="principal_amount" id="injectAmount" class="form-control" placeholder="1000.00" required>
                        </div>

                        <!-- Initial Accumulated Rewards -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Initial Accumulated Yield / Rewards ($)')</label>
                            <input type="number" step="any" name="accumulated_rewards" id="injectRewards" class="form-control" placeholder="0.00" value="0.00">
                        </div>

                        <!-- Custom Start Date -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Custom Start Date / Time')</label>
                            <input type="datetime-local" name="start_time" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                        </div>

                        <!-- Deduct Balance Toggle -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">@lang('Debit User Balance?')</label>
                            <select name="deduct_balance" class="form-control form-select">
                                <option value="0" selected>@lang('No (Direct Injection / Bonus)')</option>
                                <option value="1">@lang('Yes (Deduct from User Spot Wallet)')</option>
                            </select>
                        </div>
                    </div>

                    <!-- Calculator Summary Box -->
                    <div class="bg--dark p-3 rounded mt-2">
                        <div class="d-flex justify-content-between text--small text-white">
                            <span>@lang('Estimated Daily Yield'):</span>
                            <strong class="text--success" id="injectDailyYield">+$0.00 / day</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Confirm & Inject Stake')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Stake Modal -->
<div id="editStakeModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Edit Staking Position')</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="" method="POST" id="editStakeForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">@lang('Principal Amount ($)')</label>
                        <input type="number" step="any" name="principal_amount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('Accumulated Yield ($)')</label>
                        <input type="number" step="any" name="accumulated_rewards" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('Status')</label>
                        <select name="status" class="form-control form-select" required>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="unstaked">Unstaked</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('Start Date')</label>
                        <input type="datetime-local" name="start_time" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">@lang('Maturity / End Date (Optional)')</label>
                        <input type="datetime-local" name="end_time" class="form-control">
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
    <button type="button" class="btn btn-sm btn--outline-success injectStakeBtn">
        <i class="las la-plus-circle"></i> @lang('Inject Stake')
    </button>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";

        $('.injectStakeBtn').on('click', function () {
            $('#injectStakeModal').modal('show');
        });

        $('.editStakeBtn').on('click', function () {
            var stake = $(this).data('stake');
            var url = $(this).data('url');
            var modal = $('#editStakeModal');

            modal.find('#editStakeForm').attr('action', url);
            modal.find('input[name=principal_amount]').val(stake.principal_amount);
            modal.find('input[name=accumulated_rewards]').val(stake.accumulated_rewards);
            modal.find('select[name=status]').val(stake.status);

            if (stake.start_time) {
                var startFormatted = stake.start_time.substring(0, 16);
                modal.find('input[name=start_time]').val(startFormatted);
            }
            if (stake.end_time) {
                var endFormatted = stake.end_time.substring(0, 16);
                modal.find('input[name=end_time]').val(endFormatted);
            } else {
                modal.find('input[name=end_time]').val('');
            }

            modal.modal('show');
        });

        function calculateInjectYield() {
            var amount = parseFloat($('#injectAmount').val()) || 0;
            var selectedOption = $('#injectPoolSelect').find(':selected');
            var apy = parseFloat(selectedOption.data('apy')) || 0;

            if (amount > 0 && apy > 0) {
                var dailyYield = (amount * (apy / 100) / 365);
                $('#injectDailyYield').text('+$' + dailyYield.toFixed(4) + ' / day');
            } else {
                $('#injectDailyYield').text('+$0.00 / day');
            }
        }

        $('#injectAmount, #injectPoolSelect').on('input change', calculateInjectYield);
    })(jQuery);
</script>
@endpush
