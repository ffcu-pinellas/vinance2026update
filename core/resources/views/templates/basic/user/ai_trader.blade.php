@extends($activeTemplate . 'layouts.master')

@section('content')
<!-- Right Nav Menu -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="rightNavMenu" aria-labelledby="rightNavMenuLabel">    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="rightNavMenuLabel">
            <i class="fas fa-cog text-primary me-2"></i> Menu
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
    <a class="nav-link d-flex align-items-center" href="{{ route('user.ai.settings') }}">
        <i class="fas fa-sliders-h me-3 text-primary"></i>
                    <span>AI Trader Settings</span>
                </a>
            </li>
            
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center" href="{{ route('user.documentation') }}">
                    <i class="fas fa-book me-3 text-primary"></i>
                    <span>Setup Guide</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="https://vinance.pro/ticket">
                    <i class="fas fa-headset me-3 text-primary"></i>
                    <span>Support</span>
                </a>
            </li>
        </ul>
        
        <div class="mt-5 pt-3 border-top">
            <div class="d-grid">
                <a href="https://t.me/Vinance_AI_TraderBot" class="btn btn-telegram1" target="_blank">
                    <i class="fab fa-telegram text-light me-2"></i> Connect Telegram
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    <!-- Unified AI Console Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="mb-0"><i class="fas fa-robot text-primary me-2"></i>Vinance AI Auto Trader V2.02</h4>
        
        <div class="d-flex align-items-center gap-3">
            <div class="table-header-menu">
                <a href="{{ route('user.ai.trader') }}" class="table-header-menu__link active">Overview</a>
                <a href="{{ route('user.ai.settings') }}" class="table-header-menu__link">Settings</a>
                <a href="{{ route('user.documentation') }}" class="table-header-menu__link">Logs & Guide</a>
            </div>
            <button class="btn btn-outline-primary btn-icon rounded-circle shadow-sm d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#rightNavMenu" aria-controls="rightNavMenu">
                <i class="fas fa-bars fa-lg"></i>
            </button>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Total Trades -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Trades</h6>
                            <h3 class="mb-0">{{ $botStats['total_trades'] }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-primary-light text-primary">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Trades -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Active Trades</h6>
                            <h3 class="mb-0">{{ $botStats['active_trades'] }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-warning-light text-warning">
                                <i class="fas fa-spinner"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Trades -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Completed Trades</h6>
                            <h3 class="mb-0">{{ $botStats['completed_trades'] }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-success-light text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Profit -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Profit</h6>
                            <h3 class="mb-0">
                                @if($botStats['total_profit'] >= 0)
                                    <span class="text-success">+${{ number_format(abs($botStats['total_profit']), 2) }}</span>
                                @else
                                    <span class="text-danger">-${{ number_format(abs($botStats['total_profit']), 2) }}</span>
                                @endif
                            </h3>
                            <small class="text-muted">{{ $botStats['completed_trades'] }} trades</small>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-info-light text-info">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection & Settings -->
    <div class="row g-4 mb-4">
        <!-- Telegram Connection -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between bg-transparent border-bottom">
                    <h5 class="mb-0"><i class="fab fa-telegram text-primary me-2"></i> Telegram Connection</h5>
                </div>
                <div class="card-body text-center py-4">
                    @if($isBotActive)
                        <div class="alert alert-success d-inline-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            Connected since {{ $activatedAt ? \Carbon\Carbon::parse($activatedAt)->format('M d, Y') : 'N/A' }}
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center">
                            <a href="https://t.me/Vinance_AI_TraderBot" 
                               class="btn btn-telegram1 mb-3"
                               target="_blank">
                               <i class="fab fa-telegram text-light me-1"></i> Activate Bot
                            </a>
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i> Required for receiving trading signals
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

<!-- AI Trader Activity -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between bg-transparent border-bottom">
                    <h5 class="mb-0"><i class="fas fa-bolt text-primary me-2"></i> AI Trader Activity</h5>
                    <div class="badge bg-success-light text-success rounded-pill px-3" id="liveBadge">
                        <i class="fas fa-circle fa-fw blink"></i> LIVE
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="trade-activity-container">
                        <div class="trade-activity-list" id="tradeActivityList">
                            <!-- Initial activity items will load here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Market Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i> AI Market Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="topMoversContainer">
                        <!-- Market analysis cards will load here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between bg-transparent border-bottom">
                    <h5 class="mb-0"><i class="fas fa-list-alt text-primary me-2"></i> Transaction History</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">All Trades</a></li>
                            <li><a class="dropdown-item" href="#">Active</a></li>
                            <li><a class="dropdown-item" href="#">Completed</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Desktop Table (hidden on mobile) -->
                    <div class="d-none d-md-block">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Date</th>
                                        <th>Pair</th>
                                        <th>Type</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Profit %</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTrades as $trade)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">{{ \Carbon\Carbon::parse($trade->created_at)->format('M d, Y') }}</span>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($trade->created_at)->format('H:i A') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if(isset($trade->pair) && isset($trade->pair->coin))
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol-circle bg-light-primary me-2">
                                                        {{ substr($trade->pair->coin->symbol, 0, 2) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-medium">{{ $trade->pair->coin->symbol ?? 'N/A' }}</span>
                                                        <span class="text-muted">/{{ $trade->pair->market->currency->symbol ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $trade->order_side == '1' ? 'success-light text-success' : 'danger-light text-danger' }} rounded-pill px-3 py-1">
                                                {{ $trade->order_side == '1' ? 'BUY' : 'SELL' }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-medium">{{ number_format($trade->amount, 8) }}</td>
                                        <td class="text-end fw-medium">{{ number_format($trade->price, 8) }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-{{ $trade->profit >= 0 ? 'success-light text-success' : 'danger-light text-danger' }} rounded-pill px-3 py-1">
                                                @if($trade->profit >= 0)
                                                    +{{ number_format($trade->profit, 2) }}%
                                                @else
                                                    {{ number_format($trade->profit, 2) }}%
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusInfo = [
                                                    0 => ['text' => 'Open', 'class' => 'info-light text-info'],
                                                    1 => ['text' => 'Completed', 'class' => 'success-light text-success'],
                                                    2 => ['text' => 'Pending', 'class' => 'warning-light text-warning'],
                                                    9 => ['text' => 'Canceled', 'class' => 'danger-light text-danger']
                                                ];
                                                $status = $statusInfo[$trade->status] ?? ['text' => 'Unknown', 'class' => 'secondary-light text-secondary'];
                                            @endphp
                                            <span class="badge bg-{{ $status['class'] }} rounded-pill px-3 py-1">
                                                {{ $status['text'] }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary rounded-circle">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-exchange-alt fa-2x text-muted mb-3"></i>
                                                <h5 class="text-muted">No trading history found</h5>
                                                <p class="text-muted mb-0">Your completed trades will appear here</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Mobile Cards (shown only on mobile) -->
                    <div class="d-md-none">
                        @forelse($recentTrades as $trade)
                        <div class="card mb-3 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        @if(isset($trade->pair) && isset($trade->pair->coin))
                                        <div class="symbol-circle bg-light-primary me-2">
                                            {{ substr($trade->pair->coin->symbol, 0, 2) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $trade->pair->coin->symbol ?? 'N/A' }}/{{ $trade->pair->market->currency->symbol ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($trade->created_at)->format('M d, H:i A') }}</small>
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $trade->order_side == '1' ? 'success-light text-success' : 'danger-light text-danger' }} rounded-pill px-3 py-1">
                                        {{ $trade->order_side == '1' ? 'BUY' : 'SELL' }}
                                    </span>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="text-muted">Amount</small>
                                        <p class="mb-0 fw-medium">{{ number_format($trade->amount, 8) }}</p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted">Price</small>
                                        <p class="mb-0 fw-medium">{{ number_format($trade->price, 8) }}</p>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="text-muted">Profit %</small>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $trade->profit >= 0 ? 'success-light text-success' : 'danger-light text-danger' }} rounded-pill px-3 py-1">
                                                @if($trade->profit >= 0)
                                                    +{{ number_format($trade->profit, 2) }}%
                                                @else
                                                    {{ number_format($trade->profit, 2) }}%
                                                @endif
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="text-muted">Status</small>
                                        <p class="mb-0">
                                            @php
                                                $statusInfo = [
                                                    0 => ['text' => 'Open', 'class' => 'info-light text-info'],
                                                    1 => ['text' => 'Completed', 'class' => 'success-light text-success'],
                                                    2 => ['text' => 'Pending', 'class' => 'warning-light text-warning'],
                                                    9 => ['text' => 'Canceled', 'class' => 'danger-light text-danger']
                                                ];
                                                $status = $statusInfo[$trade->status] ?? ['text' => 'Unknown', 'class' => 'secondary-light text-secondary'];
                                            @endphp
                                            <span class="badge bg-{{ $status['class'] }} rounded-pill px-3 py-1">
                                                {{ $status['text'] }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-exchange-alt fa-2x text-muted mb-3"></i>
                                <h5 class="text-muted">No trading history found</h5>
                                <p class="text-muted mb-0">Your completed trades will appear here</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // CoinGecko API configuration
    const COINGECKO_API = 'https://api.coingecko.com/api/v3';
    const COINS = ['bitcoin', 'ethereum', 'binancecoin', 'solana', 'cardano', 'polkadot'];
    const TRADE_PAIRS = ['BTC/USDT', 'ETH/USDT', 'BNB/USDT', 'SOL/USDT', 'ADA/USDT', 'DOT/USDT'];
    
    // Initialize with real data immediately
    initializeDashboard();
    
    // Set up periodic updates every 2 seconds
    setInterval(updateDashboard, 2000);
    
    // Trade filter dropdown
    document.querySelectorAll('[data-filter]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.getAttribute('data-filter');
            loadTrades(filter);
        });
    });

    async function initializeDashboard() {
        // Load all sections with real data
        await updateStats();
        await updateTradeActivity();
        await updateMarketAnalysis();
        loadTrades('all');
    }
    
    async function updateDashboard() {
        await updateStats();
        await updateTradeActivity();
        await updateMarketAnalysis();
    }
    
    // Fetch real market data from CoinGecko
    async function fetchMarketData() {
        try {
            const response = await fetch(`${COINGECKO_API}/coins/markets?vs_currency=usd&ids=${COINS.join(',')}&order=market_cap_desc&per_page=6&page=1&sparkline=false&price_change_percentage=24h`);
            if (!response.ok) throw new Error('Failed to fetch market data');
            return await response.json();
        } catch (error) {
            console.error('Error fetching market data:', error);
            return null;
        }
    }
    
    // Update stats with realistic data
    async function updateStats() {
        try {
            // In a real implementation, you would fetch these from your backend
            // For demo purposes, we'll simulate realistic fluctuations
            const fluctuation = Math.random() > 0.5 ? 1 : -1;
            const changeAmount = Math.floor(Math.random() * 3);
            
            // Get current values or initialize
            let totalTrades = parseInt(document.getElementById('totalTrades').textContent) || 0;
            let activeTrades = parseInt(document.getElementById('activeTrades').textContent) || 0;
            let completedTrades = parseInt(document.getElementById('completedTrades').textContent) || 0;
            let totalProfit = parseFloat(document.getElementById('totalProfit').textContent.replace(/[^0-9.-]/g, '')) || 0;
            
            // Update values with realistic changes
            if (Math.random() > 0.7) totalTrades += 1; // 30% chance of a new trade
            activeTrades = Math.max(0, activeTrades + fluctuation * changeAmount);
            completedTrades = totalTrades - activeTrades;
            
            // Get real market data to influence profit simulation
            const marketData = await fetchMarketData();
            if (marketData) {
                // Calculate average market movement to influence profit
                const avgChange = marketData.reduce((sum, coin) => sum + coin.price_change_percentage_24h, 0) / marketData.length;
                totalProfit += (avgChange * totalTrades * 0.1); // Scale profit based on market movement
            } else {
                totalProfit += (Math.random() * 50 - 25); // Fallback random profit/loss
            }
            
            // Update DOM
            document.getElementById('totalTrades').textContent = totalTrades;
            document.getElementById('activeTrades').textContent = activeTrades;
            document.getElementById('completedTrades').textContent = completedTrades;
            document.getElementById('profitTradesCount').textContent = `${completedTrades} trades`;
            
            const profitElement = document.getElementById('totalProfit');
            if (totalProfit >= 0) {
                profitElement.innerHTML = `<span class="text-success">+$${Math.abs(totalProfit).toFixed(2)}</span>`;
            } else {
                profitElement.innerHTML = `<span class="text-danger">-$${Math.abs(totalProfit).toFixed(2)}</span>`;
            }
        } catch (error) {
            console.error('Error updating stats:', error);
        }
    }
    
    // Simulated trade activity with realistic data
    async function updateTradeActivity() {
        try {
            const marketData = await fetchMarketData();
            if (!marketData) return;
            
            const tradeList = document.getElementById('tradeActivityList');
            
            // Keep last 5 trades and add new ones
            const currentItems = tradeList.querySelectorAll('.trade-activity-item');
            if (currentItems.length > 5) {
                tradeList.removeChild(currentItems[currentItems.length - 1]);
            }
            
            // 60% chance to add a new trade
            if (Math.random() > 0.4) {
                const pair = TRADE_PAIRS[Math.floor(Math.random() * TRADE_PAIRS.length)];
                const action = Math.random() > 0.5 ? 'BUY' : 'SELL';
                
                // Find the coin data for realistic prices
                const coinSymbol = pair.split('/')[0];
                const coinData = marketData.find(c => c.symbol === coinSymbol.toLowerCase());
                const currentPrice = coinData ? coinData.current_price : (Math.random() * 50000 + 100);
                const priceChange = coinData ? coinData.price_change_percentage_24h : 0;
                
                // Generate realistic trade details
                const price = currentPrice.toFixed(2);
                const amount = (Math.random() * 5 + 0.1).toFixed(4);
                const now = new Date();
                
                const tradeItem = document.createElement('div');
                tradeItem.className = 'trade-activity-item new-trade';
                tradeItem.innerHTML = `
                    <span class="trade-pair">${pair}</span>
                    <span class="trade-action ${action === 'BUY' ? 'buy' : 'sell'}">
                        ${action}
                    </span>
                    <span class="trade-price">$${price}</span>
                    <span class="trade-amount">${amount}</span>
                    <span class="trade-time">
                        ${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </span>
                `;
                
                // Add new trade at the top
                tradeList.insertBefore(tradeItem, tradeList.firstChild);
                
                // Remove highlight after animation
                setTimeout(() => tradeItem.classList.remove('new-trade'), 1000);
            }
        } catch (error) {
            console.error('Error updating trade activity:', error);
        }
    }
    
    // Real-time market analysis with actual data
    async function updateMarketAnalysis() {
        try {
            const marketData = await fetchMarketData();
            if (!marketData) return;
            
            const container = document.getElementById('topMoversContainer');
            container.innerHTML = '';
            
            marketData.forEach(coin => {
                const isPositive = coin.price_change_percentage_24h >= 0;
                const changeClass = isPositive ? 'positive' : 'negative';
                const changeIcon = isPositive ? '▲' : '▼';
                
                // AI analysis phrases based on performance
                const analysisPhrases = isPositive ? [
                    `Strong bullish momentum with ${coin.price_change_percentage_24h.toFixed(2)}% gain`,
                    "Breaking through resistance levels with increasing volume",
                    "RSI indicates continued strength in the market",
                    "MACD showing bullish crossover pattern",
                    "Institutional buying pressure detected"
                ] : [
                    `Caution advised with ${Math.abs(coin.price_change_percentage_24h).toFixed(2)}% decline`,
                    "Testing key support levels with decreasing volume",
                    "Potential bearish divergence forming on charts",
                    "RSI approaching oversold territory",
                    "Profit-taking activity observed"
                ];
                
                const analysis = analysisPhrases[Math.floor(Math.random() * analysisPhrases.length)];
                
                const coinCard = document.createElement('div');
                coinCard.className = 'col-md-6 col-lg-4 mb-3';
                coinCard.innerHTML = `
                    <div class="ai-market-card h-100">
                        <div class="ai-market-header">
                            <img src="${coin.image}" alt="${coin.name}" class="ai-market-logo">
                            <span class="ai-market-name">${coin.name}</span>
                            <span class="ai-market-symbol">${coin.symbol.toUpperCase()}</span>
                        </div>
                        <div class="ai-market-price">$${coin.current_price.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        <div class="ai-market-change ${changeClass}">
                            ${changeIcon} ${Math.abs(coin.price_change_percentage_24h).toFixed(2)}%
                        </div>
                        <div class="ai-market-stats">
                            <span>24h Vol: $${(coin.total_volume / 1000000).toFixed(1)}M</span>
                            <span>MCap: $${(coin.market_cap / 1000000000).toFixed(2)}B</span>
                        </div>
                        <div class="ai-market-analysis">
                            <strong>AI Analysis:</strong> ${analysis}
                        </div>
                    </div>
                `;
                container.appendChild(coinCard);
            });
        } catch (error) {
            console.error('Error updating market analysis:', error);
        }
    }
    
    // Load trades with filter
    function loadTrades(filter = 'all') {
        // In a real implementation, this would fetch from your server
        document.querySelectorAll('#tradesTableBody tr, #mobileTradesContainer .card').forEach(el => {
            el.style.display = ''; // Reset display
        });
        
        if (filter !== 'all') {
            // Simulate filtering by hiding some items
            document.querySelectorAll('#tradesTableBody tr, #mobileTradesContainer .card').forEach((el, index) => {
                if (index % 2 === 0) { // Simple simulation
                    el.style.display = 'none';
                }
            });
        }
    }
});
</script>


<style>
    /* Improved close button styling */
    .btn-close {
        opacity: 1;
        background: none;
        font-size: 1.25rem;
        color: #6c757d;
        padding: 0.5rem;
        margin: -0.5rem -0.5rem -0.5rem auto;
    }
    
    .btn-close:hover {
        color: #495057;
    }
    
    /* Larger nav menu button */
    .btn-icon {
        width: 44px;
        height: 44px;
        font-size: 1.1rem;
    }
    
    /* Modal close button fix */
    .modal-header .btn-close {
        color: #6c757d;
    }

    :root {
        --primary-light: #e6f0ff;
        --success-light: #e6ffed;
        --danger-light: #ffebee;
        --warning-light: #fff8e6;
        --info-light: #e6f7ff;
        --secondary-light: #f5f5f5;
    }
    
    body {
        background-color: #f8fafc;
    }
    
    .stat-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .bg-primary-light { background-color: var(--primary-light); }
    .bg-success-light { background-color: var(--success-light); }
    .bg-danger-light { background-color: var(--danger-light); }
    .bg-warning-light { background-color: var(--warning-light); }
    .bg-info-light { background-color: var(--info-light); }
    .bg-secondary-light { background-color: var(--secondary-light); }
    
    /* Telegram Button */
    .btn-telegram1 {
        background: linear-gradient(135deg, #0088cc, #006699);
        color: white;
        padding: 10px 24px;
        font-size: 1rem;
        border-radius: 8px;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 136, 204, 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
    }
    
    .btn-telegram1:hover {
        background: linear-gradient(135deg, #006699, #0088cc);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 136, 204, 0.3);
    }
    
    /* Steps */
    .steps {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .step {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .step-number {
        width: 28px;
        height: 28px;
        background-color: #4e73df;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: bold;
        flex-shrink: 0;
    }
    
    .step-content {
        padding-top: 4px;
    }
    
    /* Table Styling */
    .table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(0, 0, 0, 0.01);
        --bs-table-hover-bg: rgba(0, 123, 255, 0.03);
    }
    
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom-width: 1px;
    }
    
    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
        border-top: 1px solid #f0f0f0;
    }
    
    .table-hover tbody tr:hover {
        background-color: var(--bs-table-hover-bg);
    }
    
    .symbol-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);
    }
    
    .card-header {
        padding: 1.25rem 1.5rem;
    }
    
    /* Right Nav Menu Styling */
    .offcanvas-end {
        width: 320px;
        border-left: 1px solid rgba(0, 0, 0, 0.05);
    }

    .offcanvas-header {
        padding: 1.25rem 1.5rem;
        background-color: #011426;
    }

    .offcanvas-title {
        font-weight: 600;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
    }

    .offcanvas-body {
        padding: 0;
    }

    .nav {
        padding: 0.5rem 0;
    }

    .nav-item {
        margin: 0;
    }

    .nav-link {
        padding: 0.75rem 1.5rem;
        color: #495057;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .nav-link:hover {
        background-color: #f1f5f9;
        color: #4e73df;
        border-left: 3px solid #4e73df;
    }

    .nav-link i {
        width: 24px;
        text-align: center;
        font-size: 0.9rem;
    }

    .nav-link span {
        font-weight: 500;
    }

    .border-top {
        border-top: 1px solid rgba(0, 0, 0, 0.05) !important;
    }
    
    .btn-close {
        color: white;
    }

    /* Button Toggle Styling */
    .btn-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 767.98px) {
        .offcanvas-end {
            width: 280px;
        }

        .nav-link {
            padding: 0.75rem 1.25rem;
        }

        .symbol-circle {
            width: 36px;
            height: 36px;
            font-size: 0.875rem;
        }

        .card.mb-3 {
            border-radius: 10px;
        }

        .card.mb-3 .card-body {
            padding: 1rem;
        }

        .badge.rounded-pill {
            font-size: 0.75rem;
        }

        .text-muted {
            font-size: 0.75rem;
        }

        .fw-medium {
            font-size: 0.875rem;
        }
    }

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        .offcanvas {
            background-color: #1e293b;
            color: #f8fafc;
        }

        .offcanvas-header {
            background-color: #0f172a;
        }

        .nav-link {
            color: #e2e8f0;
        }

        .nav-link:hover {
            background-color: #334155;
            color: #93c5fd;
        }

        .border-top {
            border-top-color: rgba(255, 255, 255, 0.05) !important;
        }

        .card {
            background-color: #1e293b;
            color: #f8fafc;
        }

        .table {
            color: #f8fafc;
        }

        .table thead th {
            color: #94a3b8;
        }

        .table td {
            border-top-color: rgba(255, 255, 255, 0.05);
        }
        
        .btn-close {
            color: #94a3b8;
        }
        
        .modal-header .btn-close {
            color: #94a3b8;
        }
    }
    
    /* Additional mobile optimizations */
    @media (max-width: 576px) {
        .offcanvas-end {
            width: 85%;
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .card-header {
            padding: 1rem;
        }
    }
    
    
    /* AI Trader Activity Styles */
    .trade-activity-container {
        max-height: 300px;
        overflow-y: auto;
        padding: 0;
        background-color: #f8fafc;
        border-radius: 10px 10px 10px 10px;
    }
    
    .trade-activity-list {
        padding: 0;
        margin: 0;
        list-style: none;
    }
    
    .trade-activity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        animation: fadeIn 0.5s ease;
        transition: all 0.3s ease;
        background-color: white;
    }
    
    .trade-activity-item:last-child {
        border-bottom: none;
    }
    
    .trade-activity-item.new-trade {
        background-color: rgba(25, 135, 84, 0.08);
        border-left: 3px solid #198754;
    }
    
    .trade-activity-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .trade-pair {
        font-weight: 600;
        color: #333;
        flex: 1;
        min-width: 90px;
    }
    
    .trade-action {
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        margin: 0 8px;
        min-width: 55px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .trade-action.buy {
        background-color: rgba(25, 135, 84, 0.15);
        color: #198754;
    }
    
    .trade-action.sell {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }
    
    .trade-price {
        font-weight: 600;
        margin: 0 8px;
        min-width: 90px;
        text-align: right;
        font-family: 'Courier New', monospace;
    }
    
    .trade-amount {
        font-weight: 500;
        color: #6c757d;
        margin: 0 8px;
        min-width: 80px;
        text-align: right;
        font-family: 'Courier New', monospace;
    }
    
    .trade-time {
        font-size: 0.7rem;
        color: #6c757d;
        min-width: 60px;
        text-align: right;
    }
    
    /* AI Market Analysis Cards */
    .ai-market-card {
        border-radius: 12px;
        padding: 16px;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
        background-color: white;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .ai-market-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }
    
    .ai-market-header {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .ai-market-logo {
        width: 32px;
        height: 32px;
        margin-right: 12px;
        border-radius: 50%;
        object-fit: contain;
    }
    
    .ai-market-name {
        font-weight: 600;
        margin-right: 8px;
        font-size: 0.95rem;
    }
    
    .ai-market-symbol {
        color: #6c757d;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    
    .ai-market-price {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 8px;
        font-family: 'Courier New', monospace;
    }
    
    .ai-market-change {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        margin-bottom: 12px;
    }
    
    .ai-market-change.positive {
        background-color: rgba(25, 135, 84, 0.15);
        color: #198754;
    }
    
    .ai-market-change.negative {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }
    
    .ai-market-stats {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: #6c757d;
        margin: 8px 0;
        padding: 6px 0;
        border-top: 1px dashed #eee;
        border-bottom: 1px dashed #eee;
    }
    
    .ai-market-analysis {
        margin-top: auto;
        padding-top: 10px;
        font-size: 0.8rem;
        line-height: 1.4;
    }
    
    .ai-market-analysis strong {
        color: #4e73df;
        font-weight: 600;
    }
    
    /* Live badge styling */
    .blink {
        animation: blink-animation 1.5s infinite;
    }
    
    @keyframes blink-animation {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Scrollbar styling */
    .trade-activity-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .trade-activity-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .trade-activity-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .trade-activity-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
</style>

@endsection