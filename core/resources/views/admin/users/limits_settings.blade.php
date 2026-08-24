@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 mb-4">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-tabs--custom" id="limitTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 py-3" id="deposit-tab" data-bs-toggle="tab" data-bs-target="#deposit-tab-pane" type="button" role="tab" aria-controls="deposit-tab-pane" aria-selected="true">
                            <i class="las la-wallet me-1 fs-5"></i> <strong>@lang('Deposit Gateways')</strong> <span class="badge badge--primary ms-1">{{ $gatewayCurrencies->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3" id="withdraw-tab" data-bs-toggle="tab" data-bs-target="#withdraw-tab-pane" type="button" role="tab" aria-controls="withdraw-tab-pane" aria-selected="false">
                            <i class="las la-money-bill-wave me-1 fs-5"></i> <strong>@lang('Withdrawal Methods')</strong> <span class="badge badge--dark ms-1">{{ $withdrawMethods->count() }}</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="limitTabContent">
                    {{-- Deposit Gateways Tab --}}
                    <div class="tab-pane fade show active" id="deposit-tab-pane" role="tabpanel" aria-labelledby="deposit-tab">
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 text--primary"><i class="las la-info-circle"></i> @lang('Deposit Configuration & Custom Form Settings for') {{ $user->username }}</h6>
                                <small class="text-muted">@lang('Override deposit limits, custom wallet addresses, QR codes, instructions, and user-specific form fields.')</small>
                            </div>
                        </div>
                        <div class="table-responsive--sm table-responsive">
                            <table class="table table--light style--two custom-data-table">
                                <thead>
                                    <tr>
                                        <th>@lang('Gateway')</th>
                                        <th>@lang('Currency')</th>
                                        <th>@lang('Min Amount')</th>
                                        <th>@lang('Max Amount')</th>
                                        <th>@lang('Fixed Charge')</th>
                                        <th>@lang('Percent Charge')</th>
                                        <th>@lang('Custom Form / QR')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gatewayCurrencies as $gatewayCurrency)
                                        @php
                                            $setting = $userDepositSettings->get($gatewayCurrency->id);
                                            $hasOverride = $setting ? true : false;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ __($gatewayCurrency->method->name) }}</span>
                                            </td>
                                            <td><span class="badge badge--dark">{{ __($gatewayCurrency->currency) }}</span></td>
                                            <td>
                                                @if($hasOverride)
                                                    <span class="text--success fw-bold">{{ showAmount($setting->min_amount) }} {{ __($gatewayCurrency->currency) }}</span>
                                                @else
                                                    {{ showAmount($gatewayCurrency->min_amount) }} {{ __($gatewayCurrency->currency) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasOverride)
                                                    <span class="text--success fw-bold">{{ showAmount($setting->max_amount) }} {{ __($gatewayCurrency->currency) }}</span>
                                                @else
                                                    {{ showAmount($gatewayCurrency->max_amount) }} {{ __($gatewayCurrency->currency) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasOverride)
                                                    <span class="text--success">{{ showAmount($setting->fixed_charge) }} {{ __($gatewayCurrency->currency) }}</span>
                                                @else
                                                    {{ showAmount($gatewayCurrency->fixed_charge) }} {{ __($gatewayCurrency->currency) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasOverride)
                                                    <span class="text--success">{{ showAmount($setting->percent_charge) }}%</span>
                                                @else
                                                    {{ showAmount($gatewayCurrency->percent_charge) }}%
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasOverride)
                                                    @if($setting->wallet_address)
                                                        <span class="badge badge--success"><i class="las la-qrcode"></i> @lang('Custom QR')</span>
                                                    @endif
                                                    @if($setting->form_id)
                                                        <span class="badge badge--info"><i class="las la-wpforms"></i> @lang('Custom Form')</span>
                                                    @endif
                                                    @if(!$setting->wallet_address && !$setting->form_id)
                                                        <span class="badge badge--success">@lang('Custom Limits')</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge--secondary">@lang('Global Default')</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.users.limits.settings.deposit.edit', [$user->id, $gatewayCurrency->id]) }}" class="btn btn-sm btn--primary">
                                                        <i class="la la-cog"></i> @lang('Configure')
                                                    </a>
                                                    @if($hasOverride)
                                                        <form action="{{ route('admin.users.limits.settings.deposit.remove', [$user->id, $setting->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn--danger confirmationBtn" data-question="@lang('Are you sure you want to remove this override? Global settings will be applied.')">
                                                                <i class="la la-trash"></i> @lang('Reset')
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Withdrawal Methods Tab --}}
                    <div class="tab-pane fade" id="withdraw-tab-pane" role="tabpanel" aria-labelledby="withdraw-tab">
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 text--dark"><i class="las la-info-circle"></i> @lang('Withdrawal Configuration & Custom Form Settings for') {{ $user->username }}</h6>
                                <small class="text-muted">@lang('Override withdrawal limits, custom instructions, and user-specific withdrawal submission fields.')</small>
                            </div>
                        </div>
                        <div class="table-responsive--sm table-responsive">
                            <table class="table table--light style--two custom-data-table">
                                <thead>
                                    <tr>
                                        <th>@lang('Method')</th>
                                        <th>@lang('Currency')</th>
                                        <th>@lang('Min Amount')</th>
                                        <th>@lang('Max Amount')</th>
                                        <th>@lang('Fixed Charge')</th>
                                        <th>@lang('Percent Charge')</th>
                                        <th>@lang('Custom Form')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawMethods as $method)
                                        @php
                                            $setting = $userWithdrawSettings->get($method->id);
                                            $hasOverride = $setting ? true : false;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ __($method->name) }}</span>
                                            </td>
                                            <td><span class="badge badge--dark">{{ __($method->currency) }}</span></td>
                                            <td>
                                                @if($hasOverride)
                                                    <span class="text--success fw-bold">{{ showAmount($setting->min_amount) }} {{ __($method->currency) }}</span>
                                                @else
                                                    {{ showAmount($method->min_limit) }} {{ __($method->currency) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasOverride)
                                                    <span class="text--success fw-bold">{{ showAmount($setting->max_amount) }} {{ __($method->currency) }}</span>
                                                @else
                                                    {{ showAmount($method->max_limit) }} {{ __($method->currency) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasOverride)
                                                    <span class="text--success">{{ showAmount($setting->fixed_charge) }} {{ __($method->currency) }}</span>
                                                @else
                                                    {{ showAmount($method->fixed_charge) }} {{ __($method->currency) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasOverride)
                                                    <span class="text--success">{{ showAmount($setting->percent_charge) }}%</span>
                                                @else
                                                    {{ showAmount($method->percent_charge) }}%
                                                @endif
                                            </td>
                                            <td>
                                                @if($hasOverride)
                                                    @if($setting->form_id)
                                                        <span class="badge badge--info"><i class="las la-wpforms"></i> @lang('Custom Form')</span>
                                                    @else
                                                        <span class="badge badge--success">@lang('Custom Limits')</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge--secondary">@lang('Global Default')</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.users.limits.settings.withdraw.edit', [$user->id, $method->id]) }}" class="btn btn-sm btn--primary">
                                                        <i class="la la-cog"></i> @lang('Configure')
                                                    </a>
                                                    @if($hasOverride)
                                                        <form action="{{ route('admin.users.limits.settings.withdraw.remove', [$user->id, $setting->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn--danger confirmationBtn" data-question="@lang('Are you sure you want to remove this override? Global settings will be applied.')">
                                                                <i class="la la-trash"></i> @lang('Reset')
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
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

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn-sm btn-outline--primary"><i class="las la-undo"></i> @lang('Back to User Detail')</a>
@endpush

@push('style')
<style>
    .nav-tabs--custom {
        border-bottom: 2px solid #e9ecef;
        background: #f8f9fa;
    }
    .nav-tabs--custom .nav-link {
        color: #495057;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.3s;
    }
    .nav-tabs--custom .nav-link:hover {
        color: #4634ff;
    }
    .nav-tabs--custom .nav-link.active {
        color: #4634ff;
        background: transparent;
        border-bottom: 3px solid #4634ff;
    }
</style>
@endpush
