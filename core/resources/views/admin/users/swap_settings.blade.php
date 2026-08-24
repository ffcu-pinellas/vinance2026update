@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn-sm btn--outline-dark">
                <i class="la la-arrow-left"></i> @lang('Back to User Profile')
            </a>
            <span class="badge badge--primary px-3 py-2 fs-6">
                <i class="las la-sync"></i> @lang('Coin Swap Controls') - {{ $user->username }}
            </span>
        </div>
    </div>

    <!-- User Swap Override Settings Card -->
    <div class="col-lg-5">
        <div class="card b-radius--10">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('Custom Swap Parameters & Fee Discounts')</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.swap.settings.update', $user->id) }}" method="POST">
                    @csrf
                    <!-- Custom Fee Rate Override -->
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">@lang('Custom Swap Fee Rate (%)')</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="custom_fee_percentage" class="form-control" placeholder="Default: {{ gs('swap_charge') ?? 0.10 }}%" value="{{ @$userSetting->custom_fee_percentage }}">
                            <span class="input-group-text">% Fee</span>
                        </div>
                        <small class="text-muted">@lang('Set 0.00 for 0% fee privilege, or leave empty to use default system fee.')</small>
                    </div>

                    <!-- Swap Lock Toggle -->
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">@lang('Coin Swap Feature Access')</label>
                        <select name="is_swap_locked" class="form-control form-select">
                            <option value="0" {{ @$userSetting->is_swap_locked == 0 ? 'selected' : '' }}>@lang('Enabled (Normal Access)')</option>
                            <option value="1" {{ @$userSetting->is_swap_locked == 1 ? 'selected' : '' }}>@lang('Locked (Disable Swaps for this User)')</option>
                        </select>
                        <small class="text-muted">@lang('If locked, the user will be blocked from initiating instant coin swaps.')</small>
                    </div>

                    <!-- Custom Notes -->
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">@lang('Internal Notes (Admin Only)')</label>
                        <textarea name="custom_notes" rows="3" class="form-control" placeholder="Special fee tier, VIP arrangements...">{{ @$userSetting->custom_notes }}</textarea>
                    </div>

                    <button type="submit" class="btn btn--primary w-100 h-45">
                        <i class="la la-save me-1"></i> @lang('Save User Swap Settings')
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User's Recent Swaps Card -->
    <div class="col-lg-7">
        <div class="card b-radius--10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">@lang('User Swap History') ({{ $userSwaps->count() }})</h5>
                <a href="{{ route('admin.swap.history') }}?user_id={{ $user->id }}" class="btn btn-sm btn--outline-primary">
                    <i class="las la-plus-circle"></i> @lang('Inject Swap')
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Date')</th>
                                <th>@lang('Sold (From)')</th>
                                <th>@lang('Received (To)')</th>
                                <th>@lang('Rate')</th>
                                <th>@lang('Status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userSwaps as $swap)
                                <tr>
                                    <td>
                                        <span class="small">{{ $swap->created_at->format('Y-m-d H:i') }}</span>
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
                                        <span class="small">1 {{ @$swap->fromCurrency->symbol }} ≈ {{ number_format($swap->rate, 6) }} {{ @$swap->toCurrency->symbol }}</span>
                                    </td>
                                    <td>
                                        @if($swap->status == 1)
                                            <span class="badge badge--success">@lang('Completed')</span>
                                        @else
                                            <span class="badge badge--danger">@lang('Reverted')</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">@lang('No swap records found for this user.')</td>
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
