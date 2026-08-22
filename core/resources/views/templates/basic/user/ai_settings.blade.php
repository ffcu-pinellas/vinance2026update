@extends($activeTemplate . 'layouts.master')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-sliders-h text-primary me-2"></i>Vinance AI Auto Trader V2.02 Settings</h4>
        <a href="{{ route('user.ai.trader') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Trader
        </a>
    </div>

<div class="container-fluid px-4">
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Warning:</strong> Please avoid changing these settings unless you understand their impact. 
        Incorrect settings may limit the AI Trader's potential performance.
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> Please fix the following errors:
        <ul class="mt-2 mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.ai.settings.save') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Risk Level -->
                        <div class="mb-3">
                            <label for="riskLevel" class="form-label">Risk Level</label>
                            <select class="form-select" id="riskLevel" name="risk_level">
                                <option value="low" @selected(($settings->risk_level ?? 'medium') == 'low')>Low (Conservative-Up to 20% Profit Margin)</option>
                                <option value="medium" @selected(($settings->risk_level ?? 'medium') == 'medium')>Medium (Balanced-Up to 35% Profit Margin)</option>
                                <option value="high" @selected(($settings->risk_level ?? 'medium') == 'high')>High (Aggressive-Up to 50% Profit Margin)</option>
                            </select>
                            <small class="text-muted">Higher risk means higher potential returns but also higher potential losses</small>
                        </div>
                        
                        <!-- Trading Strategy -->
                        <div class="mb-3">
                            <label for="tradingStrategy" class="form-label">Trading Strategy</label>
                            <select class="form-select" id="tradingStrategy" name="trading_strategy">
                                <option value="trend" @selected(($settings->trading_strategy ?? 'breakout') == 'trend')>Trend Following</option>
                                <option value="breakout" @selected(($settings->trading_strategy ?? 'breakout') == 'breakout')>Breakout</option>
                                <option value="scalping" @selected(($settings->trading_strategy ?? 'breakout') == 'scalping')>Scalping</option>
                                <option value="swing" @selected(($settings->trading_strategy ?? 'breakout') == 'swing')>Swing Trading</option>
                                <option value="arbitrage" @selected(($settings->trading_strategy ?? 'breakout') == 'arbitrage')>Arbitrage</option>
                            </select>
                        </div>
                        
<div class="mb-4">
    <label for="maxTrades" class="form-label d-flex justify-content-between">
        <span>Auto Trader Balance Threshold </span>
        <span class="badge bg-primary" id="maxTradesValue">{{ $settings->max_trades ?? 5 }}</span>
    </label>
    <input type="range" class="form-range custom-slider" min="1" max="100" 
           value="{{ $settings->max_trades ?? 5 }}" id="maxTrades" name="max_trades">
    <div class="d-flex justify-content-between mt-1">
        <small>1%</small>
        <small>100%</small>
    </div>
</div>

<style>
    .custom-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 8px;
        background: linear-gradient(to right, #4e73df 0%, #4e73df calc((100% - 1px) * (var(--value) - 1) / (100 - 1)), #dee2e6 calc((100% - 1px) * (var(--value) - 1) / (100 - 1)), #dee2e6 100%);
        border-radius: 4px;
    }
    .custom-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        background: #4e73df;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

<script>
    document.getElementById('maxTrades').addEventListener('input', function() {
        document.getElementById('maxTradesValue').textContent = this.value;
        this.style.setProperty('--value', this.value);
    }).style.setProperty('--value', document.getElementById('maxTrades').value);
</script>
                    </div>
                    
                    <div class="col-md-6">
                        @php
                            $tradingPairs = isset($settings->trading_pairs) 
                                ? json_decode($settings->trading_pairs, true) 
                                : ['btc', 'eth'];
                        @endphp
                        
                        <!-- Trading Pairs -->
                        <div class="mb-3">
                            <label class="form-label d-block">Trading Pairs</label>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="btcPair" 
                                               name="trading_pairs[]" value="btc" @checked(in_array('btc', $tradingPairs))>
                                        <label class="form-check-label" for="btcPair">Bitcoin (BTC)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ethPair" 
                                               name="trading_pairs[]" value="eth" @checked(in_array('eth', $tradingPairs))>
                                        <label class="form-check-label" for="ethPair">Ethereum (ETH)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="bnbPair" 
                                               name="trading_pairs[]" value="bnb" @checked(in_array('bnb', $tradingPairs))>
                                        <label class="form-check-label" for="bnbPair">BNB (BNB)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="solPair" 
                                               name="trading_pairs[]" value="sol" @checked(in_array('sol', $tradingPairs))>
                                        <label class="form-check-label" for="solPair">Solana (SOL)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="adaPair" 
                                               name="trading_pairs[]" value="ada" @checked(in_array('ada', $tradingPairs))>
                                        <label class="form-check-label" for="adaPair">Cardano (ADA)</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="xrpPair" 
                                               name="trading_pairs[]" value="xrp" @checked(in_array('xrp', $tradingPairs))>
                                        <label class="form-check-label" for="xrpPair">Ripple (XRP)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="dogePair" 
                                               name="trading_pairs[]" value="doge" @checked(in_array('doge', $tradingPairs))>
                                        <label class="form-check-label" for="dogePair">Dogecoin (DOGE)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="dotPair" 
                                               name="trading_pairs[]" value="dot" @checked(in_array('dot', $tradingPairs))>
                                        <label class="form-check-label" for="dotPair">Polkadot (DOT)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="maticPair" 
                                               name="trading_pairs[]" value="matic" @checked(in_array('matic', $tradingPairs))>
                                        <label class="form-check-label" for="maticPair">Polygon (MATIC)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="avaxPair" 
                                               name="trading_pairs[]" value="avax" @checked(in_array('avax', $tradingPairs))>
                                        <label class="form-check-label" for="avaxPair">Avalanche (AVAX)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Switches -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="telegramNotifications" 
                                   name="telegram_notifications" value="1" @checked($settings->telegram_notifications ?? true)>
                            <label class="form-check-label" for="telegramNotifications">Telegram Notifications</label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="autoTrade" 
                                   name="auto_trade" value="1" @checked($settings->auto_trade ?? true)>
                            <label class="form-check-label" for="autoTrade">Auto Trading</label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Max trades slider
        const maxTradesSlider = document.getElementById('maxTrades');
        const maxTradesValue = document.getElementById('maxTradesValue');
        
        if (maxTradesSlider && maxTradesValue) {
            maxTradesValue.textContent = maxTradesSlider.value;
            maxTradesSlider.addEventListener('input', function() {
                maxTradesValue.textContent = this.value;
            });
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 2000);
    });
</script>
@endpush
@endsection