@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4">
    <!-- Total Swaps Volume -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--primary has-link overflow-hidden box--shadow2">
            <a href="{{ route('admin.swap.history') }}" class="item-link"></a>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-sync f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Total Swaps Count')</span>
                        <h2 class="text-white">{{ number_format($totalSwapsCount) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Fees Generated -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--success has-link overflow-hidden box--shadow2">
            <a href="{{ route('admin.swap.history') }}" class="item-link"></a>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-hand-holding-usd f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Total Swap Fees Earned')</span>
                        <h2 class="text-white">${{ number_format($totalFeesGenerated, 2) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Volume -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--warning has-link overflow-hidden box--shadow2">
            <a href="{{ route('admin.swap.history') }}" class="item-link"></a>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-chart-line f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Estimated Swap Volume')</span>
                        <h2 class="text-white">${{ number_format($totalVolumeUsd, 2) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Traders -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--info has-link overflow-hidden box--shadow2">
            <a href="{{ route('admin.swap.history') }}" class="item-link"></a>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-users f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Unique Swap Traders')</span>
                        <h2 class="text-white">{{ number_format($totalUniqueTraders) }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Swaps Stream -->
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">@lang('Recent Coin Swaps')</h5>
                <a href="{{ route('admin.swap.history') }}" class="btn btn-sm btn--primary">
                    <i class="las la-list"></i> @lang('View All Swaps & Injector')
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Sold (From)')</th>
                                <th>@lang('Received (To)')</th>
                                <th>@lang('Execution Rate')</th>
                                <th>@lang('Fee')</th>
                                <th>@lang('Date & Time')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSwaps as $swap)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ @$swap->user->fullname }}</span>
                                        <br>
                                        <span class="small">
                                            <a href="{{ route('admin.users.detail', @$swap->user_id) }}"><span>@</span>{{ @$swap->user->username }}</a>
                                        </span>
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
                                        <span>1 {{ @$swap->fromCurrency->symbol }} ≈ {{ number_format($swap->rate, 6) }} {{ @$swap->toCurrency->symbol }}</span>
                                    </td>
                                    <td>
                                        <span>{{ number_format($swap->charge, 6) }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $swap->created_at->format('Y-m-d H:i') }}</span>
                                    </td>
                                    <td>
                                        @if($swap->status == 1)
                                            <span class="badge badge--success">@lang('Completed')</span>
                                        @else
                                            <span class="badge badge--danger">@lang('Reverted')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.swap.history') }}?user_id={{ $swap->user_id }}" class="btn btn-sm btn--outline-primary">
                                            <i class="la la-eye"></i> @lang('Details')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">@lang('No coin swap transactions recorded yet')</td>
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
