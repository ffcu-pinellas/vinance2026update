@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">@lang('Custom Settings for') {{ $user->username }}</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="deposit-tab" data-bs-toggle="tab" data-bs-target="#deposit" type="button" role="tab" aria-controls="deposit" aria-selected="true">@lang('Deposit Settings')</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="withdraw-tab" data-bs-toggle="tab" data-bs-target="#withdraw" type="button" role="tab" aria-controls="withdraw" aria-selected="false">@lang('Withdraw Settings')</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-4" id="myTabContent">
                        <div class="tab-pane fade show active" id="deposit" role="tabpanel" aria-labelledby="deposit-tab">
                            <div class="table-responsive--sm table-responsive">
                                <table class="table table--light style--two custom-data-table">
                                    <thead>
                                    <tr>
                                        <th>@lang('Gateway')</th>
                                        <th>@lang('Currency')</th>
                                        <th>@lang('Min / Max')</th>
                                        <th>@lang('Charge (Fixed + Percent)')</th>
                                        <th>@lang('Custom Form')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($gatewayCurrencies as $gc)
                                        @php
                                            $override = $userDepositSettings->get($gc->id);
                                        @endphp
                                        <tr>
                                            <td>{{ __($gc->name) }}</td>
                                            <td>{{ __($gc->currency) }}</td>
                                            <td>
                                                @if($override)
                                                    <span class="text--success">{{ showAmount($override->min_amount) }} - {{ showAmount($override->max_amount) }}</span>
                                                    <br><small class="text-muted">Global: {{ showAmount($gc->min_amount) }} - {{ showAmount($gc->max_amount) }}</small>
                                                @else
                                                    {{ showAmount($gc->min_amount) }} - {{ showAmount($gc->max_amount) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($override)
                                                    <span class="text--success">{{ showAmount($override->fixed_charge) }} + {{ showAmount($override->percent_charge) }}%</span>
                                                    <br><small class="text-muted">Global: {{ showAmount($gc->fixed_charge) }} + {{ showAmount($gc->percent_charge) }}%</small>
                                                @else
                                                    {{ showAmount($gc->fixed_charge) }} + {{ showAmount($gc->percent_charge) }}%
                                                @endif
                                            </td>
                                            <td>
                                                @if($override && $override->form_id)
                                                    <span class="badge badge--success">@lang('Yes')</span>
                                                @else
                                                    <span class="badge badge--danger">@lang('No')</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline--primary editBtn" 
                                                    data-id="{{ $gc->id }}"
                                                    data-min="{{ $override ? $override->min_amount : $gc->min_amount }}"
                                                    data-max="{{ $override ? $override->max_amount : $gc->max_amount }}"
                                                    data-fixed="{{ $override ? $override->fixed_charge : $gc->fixed_charge }}"
                                                    data-percent="{{ $override ? $override->percent_charge : $gc->percent_charge }}"
                                                    data-form="{{ $override ? $override->form_id : '' }}"
                                                    data-wallet="{{ $override ? $override->wallet_address : '' }}"
                                                    data-info="{{ $override ? $override->payment_info : '' }}"
                                                    data-target="#depositModal">
                                                    <i class="la la-pencil"></i> @lang('Override')
                                                </button>
                                                @if($override)
                                                    <form action="{{ route('admin.users.limits.settings.deposit.remove', [$user->id, $override->id]) }}" method="POST" class="d-inline-block">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline--danger"><i class="la la-trash"></i> @lang('Reset')</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="withdraw" role="tabpanel" aria-labelledby="withdraw-tab">
                            <div class="table-responsive--sm table-responsive">
                                <table class="table table--light style--two custom-data-table">
                                    <thead>
                                    <tr>
                                        <th>@lang('Method')</th>
                                        <th>@lang('Currency')</th>
                                        <th>@lang('Min / Max')</th>
                                        <th>@lang('Charge (Fixed + Percent)')</th>
                                        <th>@lang('Custom Form')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($withdrawMethods as $wm)
                                        @php
                                            $override = $userWithdrawSettings->get($wm->id);
                                        @endphp
                                        <tr>
                                            <td>{{ __($wm->name) }}</td>
                                            <td>{{ __($wm->currency) }}</td>
                                            <td>
                                                @if($override)
                                                    <span class="text--success">{{ showAmount($override->min_amount) }} - {{ showAmount($override->max_amount) }}</span>
                                                    <br><small class="text-muted">Global: {{ showAmount($wm->min_limit) }} - {{ showAmount($wm->max_limit) }}</small>
                                                @else
                                                    {{ showAmount($wm->min_limit) }} - {{ showAmount($wm->max_limit) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($override)
                                                    <span class="text--success">{{ showAmount($override->fixed_charge) }} + {{ showAmount($override->percent_charge) }}%</span>
                                                    <br><small class="text-muted">Global: {{ showAmount($wm->fixed_charge) }} + {{ showAmount($wm->percent_charge) }}%</small>
                                                @else
                                                    {{ showAmount($wm->fixed_charge) }} + {{ showAmount($wm->percent_charge) }}%
                                                @endif
                                            </td>
                                            <td>
                                                @if($override && $override->form_id)
                                                    <span class="badge badge--success">@lang('Yes')</span>
                                                @else
                                                    <span class="badge badge--danger">@lang('No')</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline--primary editBtn" 
                                                    data-id="{{ $wm->id }}"
                                                    data-min="{{ $override ? $override->min_amount : $wm->min_limit }}"
                                                    data-max="{{ $override ? $override->max_amount : $wm->max_limit }}"
                                                    data-fixed="{{ $override ? $override->fixed_charge : $wm->fixed_charge }}"
                                                    data-percent="{{ $override ? $override->percent_charge : $wm->percent_charge }}"
                                                    data-form="{{ $override ? $override->form_id : '' }}"
                                                    data-wallet="{{ $override ? $override->wallet_address : '' }}"
                                                    data-info="{{ $override ? $override->payment_info : '' }}"
                                                    data-target="#withdrawModal">
                                                    <i class="la la-pencil"></i> @lang('Override')
                                                </button>
                                                @if($override)
                                                    <form action="{{ route('admin.users.limits.settings.withdraw.remove', [$user->id, $override->id]) }}" method="POST" class="d-inline-block">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline--danger"><i class="la la-trash"></i> @lang('Reset')</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deposit Override Modal --}}
    <div id="depositModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Override Deposit Settings')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.users.limits.settings.deposit.update', $user->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="gateway_currency_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Minimum Amount')</label>
                            <input type="number" step="any" name="min_amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Maximum Amount')</label>
                            <input type="number" step="any" name="max_amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Fixed Charge')</label>
                            <input type="number" step="any" name="fixed_charge" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Percent Charge')</label>
                            <input type="number" step="any" name="percent_charge" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Custom Form (Optional)')</label>
                            <select name="form_id" class="form-control" data-search="false" style="width: 100%;">
                                <option value="">@lang('No Override (Use Default)')</option>
                                @foreach($forms as $form)
                                    <option value="{{ $form->id }}">{{ $form->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('User-Specific Wallet Address (Optional)')</label>
                            <input type="text" name="wallet_address" class="form-control" placeholder="e.g. 0x123...">
                            <small class="text-muted">@lang('Provide a unique deposit wallet for this user. Will auto-generate QR.')</small>
                        </div>
                        <div class="form-group">
                            <label>@lang('User-Specific Payment Instructions (Optional)')</label>
                            <textarea name="payment_info" class="form-control" rows="3" placeholder="Special instructions for this user..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Withdraw Override Modal --}}
    <div id="withdrawModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Override Withdraw Settings')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.users.limits.settings.withdraw.update', $user->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="withdraw_method_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Minimum Amount')</label>
                            <input type="number" step="any" name="min_amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Maximum Amount')</label>
                            <input type="number" step="any" name="max_amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Fixed Charge')</label>
                            <input type="number" step="any" name="fixed_charge" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Percent Charge')</label>
                            <input type="number" step="any" name="percent_charge" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Custom Form (Optional)')</label>
                            <select name="form_id" class="form-control" data-search="false" style="width: 100%;">
                                <option value="">@lang('No Override (Use Default)')</option>
                                @foreach($forms as $form)
                                    <option value="{{ $form->id }}">{{ $form->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('User-Specific Wallet/Account Info (Optional)')</label>
                            <input type="text" name="wallet_address" class="form-control" placeholder="Specific account details...">
                        </div>
                        <div class="form-group">
                            <label>@lang('User-Specific Payment Instructions (Optional)')</label>
                            <textarea name="payment_info" class="form-control" rows="3" placeholder="Special withdrawal instructions for this user..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function ($) {
            "use strict";
            $('.editBtn').on('click', function () {
                var modalId = $(this).data('target');
                var modal = $(modalId);
                var isDeposit = modalId === '#depositModal';
                
                if(isDeposit) {
                    modal.find('input[name=gateway_currency_id]').val($(this).data('id'));
                } else {
                    modal.find('input[name=withdraw_method_id]').val($(this).data('id'));
                }
                
                modal.find('input[name=min_amount]').val($(this).data('min'));
                modal.find('input[name=max_amount]').val($(this).data('max'));
                modal.find('input[name=fixed_charge]').val($(this).data('fixed'));
                modal.find('input[name=percent_charge]').val($(this).data('percent'));
                modal.find('select[name=form_id]').val($(this).data('form'));
                modal.find('input[name=wallet_address]').val($(this).data('wallet'));
                modal.find('textarea[name=payment_info]').val($(this).data('info'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
