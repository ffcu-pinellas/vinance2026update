@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn-sm btn--outline-dark">
                <i class="la la-arrow-left"></i> @lang('Back to User Profile')
            </a>
            <span class="badge badge--primary px-3 py-2 fs-6">
                <i class="las la-coins"></i> @lang('Staking Custom Controls') - {{ $user->username }}
            </span>
        </div>
    </div>

    <!-- User Staking Override Settings Card -->
    <div class="col-lg-5">
        <div class="card b-radius--10">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('Custom Staking Parameters & VIP Boost')</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.staking.update', $user->id) }}" method="POST">
                    @csrf
                    <!-- VIP APY Booster -->
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">@lang('Custom VIP APY Boost Rate (%)')</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="custom_apy_boost" class="form-control" placeholder="e.g. 5.00" value="{{ @$userSetting->custom_apy_boost }}">
                            <span class="input-group-text">% APY Extra</span>
                        </div>
                        <small class="text-muted">@lang('Adds extra yield percentage to all staking pools for this specific user.')</small>
                    </div>

                    <!-- Force Lock Exemption Toggle -->
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">@lang('Locked Vault Early Unstake Exemption')</label>
                        <select name="force_lock_exemption" class="form-control form-select">
                            <option value="0" {{ @$userSetting->force_lock_exemption == 0 ? 'selected' : '' }}>@lang('Standard (Enforce Lock & Penalties)')</option>
                            <option value="1" {{ @$userSetting->force_lock_exemption == 1 ? 'selected' : '' }}>@lang('Exempt (Allow Free Early Unstake Anytime)')</option>
                        </select>
                        <small class="text-muted">@lang('If exempt, user can unstake locked vaults anytime without early exit penalties.')</small>
                    </div>

                    <!-- Custom Admin Notes -->
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">@lang('Internal Notes (Admin Only)')</label>
                        <textarea name="custom_notes" rows="3" class="form-control" placeholder="Special terms, VIP arrangement notes...">{{ @$userSetting->custom_notes }}</textarea>
                    </div>

                    <button type="submit" class="btn btn--primary w-100 h-45">
                        <i class="la la-save me-1"></i> @lang('Save User Staking Settings')
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User's Active & Past Stakes Card -->
    <div class="col-lg-7">
        <div class="card b-radius--10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">@lang('User Active & Past Stakes') ({{ $userStakes->count() }})</h5>
                <a href="{{ route('admin.staking.stakes') }}?user_id={{ $user->id }}" class="btn btn-sm btn--outline-primary">
                    <i class="las la-plus-circle"></i> @lang('Inject Stake')
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Vault Pool')</th>
                                <th>@lang('Principal')</th>
                                <th>@lang('APY Rate')</th>
                                <th>@lang('Yield Earned')</th>
                                <th>@lang('Status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userStakes as $stake)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ @$stake->pool->name ?? 'Staking Vault' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">${{ number_format($stake->principal_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge--info">{{ @$stake->pool->apy_rate }}% APY</span>
                                    </td>
                                    <td>
                                        <span class="text--success fw-bold">+${{ number_format($stake->accumulated_rewards, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($stake->status == 'active')
                                            <span class="badge badge--success">@lang('Active')</span>
                                        @else
                                            <span class="badge badge--dark">{{ ucfirst($stake->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">@lang('No staking positions found for this user.')</td>
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
