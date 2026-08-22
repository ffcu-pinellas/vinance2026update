@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Token')</th>
                                <th>@lang('Min Amount')</th>
                                <th>@lang('Max Amount')</th>
                                <th>@lang('Penalty %')</th>
                                <th>@lang('Compounds')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tokens as $token)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $token->token_symbol }}</span>
                                    <br>
                                    <small>{{ $token->token_name }}</small>
                                </td>
                                <td>{{ showAmount($token->min_amount) }} {{ $general->cur_text }}</td>
                                <td>{{ showAmount($token->max_amount) }} {{ $general->cur_text }}</td>
                                <td>{{ $token->early_unstake_penalty_percentage }}%</td>
                                <td>
                                    @if($token->allows_compound)
                                        <span class="badge badge--success">@lang('Enabled')</span>
                                    @else
                                        <span class="badge badge--danger">@lang('Disabled')</span>
                                    @endif
                                </td>
                                <td>
                                    @if($token->is_active)
                                        <span class="badge badge--success">@lang('Active')</span>
                                    @else
                                        <span class="badge badge--danger">@lang('Inactive')</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline--primary editToken" 
                                            data-token="{{ $token }}"
                                            data-toggle="modal" 
                                            data-target="#editTokenModal">
                                        <i class="la la-pencil"></i> @lang('Edit')
                                    </button>
                                    <button class="btn btn-sm btn-outline--success addPool"
                                            data-token="{{ $token->id }}"
                                            data-toggle="modal"
                                            data-target="#addPoolModal">
                                        <i class="la la-plus"></i> @lang('Add Pool')
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add New Token Modal --}}
<div class="modal fade" id="addTokenModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add New Staking Token')</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.staking.token.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Token Symbol')</label>
                        <input type="text" class="form-control" name="token_symbol" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Token Name')</label>
                        <input type="text" class="form-control" name="token_name" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Minimum Amount')</label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control" name="min_amount" required>
                            <span class="input-group-text">{{ $general->cur_text }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>@lang('Maximum Amount')</label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control" name="max_amount" required>
                            <span class="input-group-text">{{ $general->cur_text }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>@lang('Early Unstake Penalty')</label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control" name="early_unstake_penalty_percentage" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>@lang('Allow Compound')</label>
                        <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-toggle="toggle" data-on="@lang('Enabled')" data-off="@lang('Disabled')" name="allows_compound">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn--primary">@lang('Save')</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add New Pool Modal --}}
<div class="modal fade" id="addPoolModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add New Staking Pool')</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.staking.pool.store') }}" method="POST">
                @csrf
                <input type="hidden" name="configuration_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Pool Type')</label>
                        <select class="form-control" name="type" required>
                            <option value="flexible">@lang('Flexible')</option>
                            <option value="locked">@lang('Locked')</option>
                        </select>
                    </div>
                    <div class="form-group locked-period d-none">
                        <label>@lang('Lock Period (Days)')</label>
                        <input type="number" class="form-control" name="lock_period_days">
                    </div>
                    <div class="form-group">
                        <label>@lang('APY Rate')</label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control" name="apy_rate" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn--primary">@lang('Save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<button class="btn btn-sm btn--primary box--shadow1 text--small addToken" data-toggle="modal" data-target="#addTokenModal">
    <i class="fa fa-fw fa-plus"></i>@lang('Add New Token')
</button>
@endpush

@push('script')
<script>
    (function($){
        "use strict"
        
        $('.addToken').on('click', function() {
            var modal = $('#addTokenModal');
            modal.modal('show');
        });

        $('.editToken').on('click', function() {
            var modal = $('#editTokenModal');
            var token = $(this).data('token');
            
            modal.find('input[name=token_symbol]').val(token.token_symbol);
            modal.find('input[name=token_name]').val(token.token_name);
            modal.find('input[name=min_amount]').val(token.min_amount);
            modal.find('input[name=max_amount]').val(token.max_amount);
            modal.find('input[name=early_unstake_penalty_percentage]').val(token.early_unstake_penalty_percentage);
            modal.find('input[name=allows_compound]').prop('checked', token.allows_compound);
            
            modal.modal('show');
        });

        $('.addPool').on('click', function() {
            var modal = $('#addPoolModal');
            var tokenId = $(this).data('token');
            
            modal.find('input[name=configuration_id]').val(tokenId);
            modal.modal('show');
        });

        $('select[name=type]').on('change', function() {
            if($(this).val() == 'locked') {
                $('.locked-period').removeClass('d-none');
            } else {
                $('.locked-period').addClass('d-none');
            }
        });
    })(jQuery);
</script>
@endpush