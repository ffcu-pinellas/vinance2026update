@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header bg--primary">
                <h5 class="text-white mb-0">@lang('Deposit Limits Override') for {{ $user->username }}</h5>
            </div>
            <div class="card-body p-0">
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
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gatewayCurrencies as $gatewayCurrency)
                                @php
                                    $setting = $userDepositSettings->where('gateway_currency_id', $gatewayCurrency->id)->first();
                                    $hasOverride = $setting ? true : false;
                                @endphp
                                <tr>
                                    <td>{{ __($gatewayCurrency->method->name) }}</td>
                                    <td>{{ __($gatewayCurrency->currency) }}</td>
                                    <td>
                                        @if($hasOverride)
                                            <span class="text--success">{{ showAmount($setting->min_amount) }} {{ __($gatewayCurrency->currency) }}</span>
                                        @else
                                            {{ showAmount($gatewayCurrency->min_amount) }} {{ __($gatewayCurrency->currency) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasOverride)
                                            <span class="text--success">{{ showAmount($setting->max_amount) }} {{ __($gatewayCurrency->currency) }}</span>
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
                                            <span class="badge badge--success">@lang('Overridden')</span>
                                        @else
                                            <span class="badge badge--primary">@lang('Global')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.limits.settings.deposit.edit', [$user->id, $gatewayCurrency->id]) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="la la-pencil"></i> @lang('Configure')
                                        </a>
                                        @if($hasOverride)
                                            <form action="{{ route('admin.users.limits.settings.deposit.remove', [$user->id, $setting->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger confirmationBtn" data-question="@lang('Are you sure you want to remove this override? The global settings will be applied instead.')">
                                                    <i class="la la-trash"></i> @lang('Reset')
                                                </button>
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

        <div class="card">
            <div class="card-header bg--primary">
                <h5 class="text-white mb-0">@lang('Withdrawal Limits Override') for {{ $user->username }}</h5>
            </div>
            <div class="card-body p-0">
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
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($withdrawMethods as $method)
                                @php
                                    $setting = $userWithdrawSettings->where('withdraw_method_id', $method->id)->first();
                                    $hasOverride = $setting ? true : false;
                                @endphp
                                <tr>
                                    <td>{{ __($method->name) }}</td>
                                    <td>{{ __($method->currency) }}</td>
                                    <td>
                                        @if($hasOverride)
                                            <span class="text--success">{{ showAmount($setting->min_amount) }} {{ __($method->currency) }}</span>
                                        @else
                                            {{ showAmount($method->min_limit) }} {{ __($method->currency) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasOverride)
                                            <span class="text--success">{{ showAmount($setting->max_amount) }} {{ __($method->currency) }}</span>
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
                                            <span class="badge badge--success">@lang('Overridden')</span>
                                        @else
                                            <span class="badge badge--primary">@lang('Global')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.limits.settings.withdraw.edit', [$user->id, $method->id]) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="la la-pencil"></i> @lang('Configure')
                                        </a>
                                        @if($hasOverride)
                                            <form action="{{ route('admin.users.limits.settings.withdraw.remove', [$user->id, $setting->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger confirmationBtn" data-question="@lang('Are you sure you want to remove this override? The global settings will be applied instead.')">
                                                    <i class="la la-trash"></i> @lang('Reset')
                                                </button>
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

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn-sm btn-outline--primary"><i class="las la-undo"></i> @lang('Back')</a>
@endpush
