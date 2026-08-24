@extends('admin.layouts.app')

@section('panel')
<div class="row gy-4 mb-4">
    <!-- Total Plans -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--primary has-link overflow-hidden box--shadow2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-robot f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Active Bot Strategies')</span>
                        <h2 class="text-white">{{ $activePlans }} / {{ $totalPlans }}</h2>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.ai.plans') }}" class="btn text--small bg--white text--black w-100 btn-outline--light">@lang('Manage Strategies') <i class="las la-arrow-right"></i></a>
        </div>
    </div>

    <!-- Active User Bots -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--success has-link overflow-hidden box--shadow2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-play-circle f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Running User Bots')</span>
                        <h2 class="text-white">{{ $activeUserBots }}</h2>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.ai.trades') }}" class="btn text--small bg--white text--black w-100 btn-outline--light">@lang('Monitor Trades') <i class="las la-arrow-right"></i></a>
        </div>
    </div>

    <!-- Total Capital Allocated -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--dark has-link overflow-hidden box--shadow2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-wallet f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Total Capital Allocated')</span>
                        <h2 class="text-white">${{ showAmount($totalCapitalAllocated) }}</h2>
                    </div>
                </div>
            </div>
            <div class="btn text--small bg--white text--black w-100 btn-outline--light opacity-75">@lang('Active Trading Capital')</div>
        </div>
    </div>

    <!-- Total Profit Generated -->
    <div class="col-xxl-3 col-sm-6">
        <div class="card bg--info has-link overflow-hidden box--shadow2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4">
                        <i class="las la-chart-line f-size--56 text-white"></i>
                    </div>
                    <div class="col-8 text-end">
                        <span class="text-white text--small">@lang('Total Bot Profits')</span>
                        <h2 class="text-white">${{ showAmount($totalProfitGenerated) }}</h2>
                    </div>
                </div>
            </div>
            <div class="btn text--small bg--white text--black w-100 btn-outline--light opacity-75">@lang('Cumulative Realized PnL')</div>
        </div>
    </div>
</div>

<div class="row gy-4">
    <!-- Active User Bots List -->
    <div class="col-lg-7">
        <div class="card b-radius--10">
            <div class="card-header bg--primary d-flex justify-content-between align-items-center">
                <h5 class="text-white mb-0"><i class="las la-running"></i> @lang('Active User AI Bots')</h5>
                <a href="{{ route('admin.ai.plans') }}" class="btn btn-sm btn-outline-light"><i class="las la-plus"></i> @lang('New Strategy')</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Strategy Plan')</th>
                                <th>@lang('Allocated')</th>
                                <th>@lang('Profit')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUserBots as $userBot)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ @$userBot->user->username }}</span>
                                        <br><small class="text-muted">{{ @$userBot->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge--dark">{{ @$userBot->plan->name ?? 'Custom Plan' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">${{ showAmount($userBot->allocated_amount) }}</span>
                                    </td>
                                    <td>
                                        <span class="text--success fw-bold">+${{ showAmount($userBot->current_profit) }}</span>
                                    </td>
                                    <td>
                                        @if($userBot->status == 1)
                                            <span class="badge badge--success"><i class="las la-spinner la-spin"></i> @lang('Running')</span>
                                        @else
                                            <span class="badge badge--warning">@lang('Paused')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.ai.settings', $userBot->user_id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-cog"></i> @lang('Configure')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">@lang('No active user bots deployed yet')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent AI Trades & Quick Inject -->
    <div class="col-lg-5">
        <div class="card b-radius--10">
            <div class="card-header bg--primary d-flex justify-content-between align-items-center">
                <h5 class="text-white mb-0"><i class="las la-bolt"></i> @lang('Recent AI Trades')</h5>
                <a href="{{ route('admin.ai.trades') }}" class="btn btn-sm btn-outline-light"><i class="las la-list"></i> @lang('View All')</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--sm table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Pair / Side')</th>
                                <th>@lang('Profit')</th>
                                <th>@lang('Time')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTrades as $trade)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ @$trade->user->username }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $trade->pair_symbol }}</span>
                                        <span class="badge badge--{{ $trade->side == 'BUY' ? 'success' : 'danger' }} ms-1">{{ $trade->side }}</span>
                                    </td>
                                    <td>
                                        <span class="text--success fw-bold">+${{ showAmount($trade->profit_amount) }}</span>
                                        <small class="text-muted d-block">(+{{ $trade->profit_percentage }}%)</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ showDateTime($trade->created_at, 'M d, H:i') }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">@lang('No trade logs recorded yet')</td>
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
    <a href="{{ route('admin.ai.plans') }}" class="btn btn-sm btn-outline--primary me-2"><i class="las la-sliders-h"></i> @lang('Bot Strategies')</a>
    <a href="{{ route('admin.ai.trades') }}" class="btn btn-sm btn-outline--info"><i class="las la-chart-bar"></i> @lang('Trade Logs & Inject')</a>
@endpush
