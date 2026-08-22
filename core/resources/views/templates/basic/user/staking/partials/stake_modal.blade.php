<div class="modal fade" id="stakeModal" tabindex="-1" aria-labelledby="stakeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stakeModalLabel">@lang('Stake USDT')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.staking.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="pool_id">
                    
                    <!-- Pool Details -->
                    <div class="pool-details mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="pool-name mb-1">-</h6>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-primary pool-type">-</span>
                                    <span class="badge bg-success pool-apy">- APY</span>
                                </div>
                            </div>
                            <img src="{{ asset('assets/images/currency/67d332468fda71741894214.jpeg') }}" alt="USDT" style="width: 40px;">
                        </div>
                        <div class="lock-period-info small text-muted mb-2" style="display: none;">
                            @lang('Lock Period'): <span class="lock-days">-</span> @lang('days')
                        </div>
                    </div>

                    <!-- Available Balance -->
                    <div class="form-group mb-3">
                        <label class="d-flex justify-content-between">
                            @lang('Available Balance')
                            <span class="text-muted">
                                @lang('Total'): {{ showAmount(($fundingWallet->balance ?? 0) + ($spotWallet->balance ?? 0)) }} USDT
                            </span>
                        </label>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" value="{{ showAmount($fundingWallet->balance ?? 0) }} USDT" disabled>
                            <span class="input-group-text" data-bs-toggle="tooltip" title="@lang('Funding wallet balance')">
                                @lang('Funding')
                            </span>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ showAmount($spotWallet->balance ?? 0) }} USDT" disabled>
                            <span class="input-group-text" data-bs-toggle="tooltip" title="@lang('Spot wallet balance')">
                                @lang('Spot')
                            </span>
                        </div>
                    </div>

                    <!-- Stake Amount -->
                    <div class="form-group mb-3">
                        <label class="d-flex justify-content-between">
                            @lang('Stake Amount')
                            <a href="javascript:void(0)" class="text-primary stake-max">@lang('Max')</a>
                        </label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control" name="principal_amount" required>
                            <span class="input-group-text">USDT</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">@lang('Min'): <span class="min-amount">0</span> USDT</small>
                            <small class="text-muted">@lang('Max'): <span class="max-amount">0</span> USDT</small>
                        </div>
                    </div>

                    <!-- Earnings Calculator -->
                    <div class="earnings-calculator border-top pt-3 mt-3">
                        <h6 class="mb-3">@lang('Earnings Calculator')</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="info-item">
                                    <small data-bs-toggle="tooltip" title="@lang('Daily earnings based on current APY')">
                                        @lang('Daily Earnings')
                                    </small>
                                    <h6 class="daily-earnings">0.00 USDT</h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-item">
                                    <small data-bs-toggle="tooltip" title="@lang('Monthly earnings based on current APY')">
                                        @lang('Monthly Earnings')
                                    </small>
                                    <h6 class="monthly-earnings">0.00 USDT</h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-item">
                                    <small data-bs-toggle="tooltip" title="@lang('Yearly earnings based on current APY')">
                                        @lang('Yearly Earnings')
                                    </small>
                                    <h6 class="yearly-earnings">0.00 USDT</h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-item">
                                    <small data-bs-toggle="tooltip" title="@lang('Total value after one year')">
                                        @lang('Total Value')
                                    </small>
                                    <h6 class="total-value">0.00 USDT</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn-primary">@lang('Stake Now')</button>
                </div>
            </form>
        </div>
    </div>
</div>