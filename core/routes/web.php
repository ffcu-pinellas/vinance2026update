<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\P2P\AdvertisementController;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\TelegramActivationController;
use App\Http\Controllers\User\AITraderController;
use App\Http\Controllers\User\CoinSwapController;
use App\Http\Controllers\User\StakingController;

Route::get('/clear', function () {
    try {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('gateways', 'wallet_address')) {
            \Illuminate\Support\Facades\Schema::table('gateways', function ($table) {
                $table->string('wallet_address')->nullable();
            });
        }
        
        if (!\Illuminate\Support\Facades\Schema::hasTable('user_withdraw_settings')) {
            \Illuminate\Support\Facades\Schema::create('user_withdraw_settings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('withdraw_method_id');
                $table->decimal('min_limit', 28, 8)->nullable();
                $table->decimal('max_limit', 28, 8)->nullable();
                $table->decimal('fixed_charge', 28, 8)->nullable();
                $table->decimal('percent_charge', 28, 8)->nullable();
                $table->unsignedBigInteger('form_id')->nullable();
                $table->string('form_title')->nullable();
                $table->string('wallet_address')->nullable();
                $table->text('payment_info')->nullable();
                $table->timestamps();
            });
        } else {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_withdraw_settings', 'withdraw_method_id')) {
                \Illuminate\Support\Facades\Schema::table('user_withdraw_settings', function ($table) {
                    $table->unsignedBigInteger('withdraw_method_id')->nullable()->after('user_id');
                });
                if (\Illuminate\Support\Facades\Schema::hasColumn('user_withdraw_settings', 'method_id')) {
                    \Illuminate\Support\Facades\DB::statement("UPDATE user_withdraw_settings SET withdraw_method_id = method_id WHERE withdraw_method_id IS NULL");
                }
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_withdraw_settings', 'payment_info')) {
                \Illuminate\Support\Facades\Schema::table('user_withdraw_settings', function ($table) {
                    $table->string('wallet_address')->nullable();
                    $table->text('payment_info')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_withdraw_settings', 'form_title')) {
                \Illuminate\Support\Facades\Schema::table('user_withdraw_settings', function ($table) {
                    $table->string('form_title')->nullable();
                });
            }
        }
        
        if (!\Illuminate\Support\Facades\Schema::hasTable('user_deposit_settings')) {
            \Illuminate\Support\Facades\Schema::create('user_deposit_settings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('gateway_currency_id');
                $table->decimal('min_amount', 28, 8)->nullable();
                $table->decimal('max_amount', 28, 8)->nullable();
                $table->decimal('fixed_charge', 28, 8)->nullable();
                $table->decimal('percent_charge', 28, 8)->nullable();
                $table->unsignedBigInteger('form_id')->nullable();
                $table->string('form_title')->nullable();
                $table->string('wallet_address')->nullable();
                $table->text('payment_info')->nullable();
                $table->timestamps();
            });
        } else {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_deposit_settings', 'payment_info')) {
                \Illuminate\Support\Facades\Schema::table('user_deposit_settings', function ($table) {
                    $table->string('wallet_address')->nullable();
                    $table->text('payment_info')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_deposit_settings', 'form_title')) {
                \Illuminate\Support\Facades\Schema::table('user_deposit_settings', function ($table) {
                    $table->string('form_title')->nullable();
                });
            }
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('deposits', 'gateway_currency_id')) {
            \Illuminate\Support\Facades\Schema::table('deposits', function ($table) {
                $table->unsignedBigInteger('gateway_currency_id')->nullable()->after('method_code');
            });
        }

        // Seed or Update Chatwoot Extension
        $chatwootScript = '<script>
  window.chatwootSettings = {
    position: "right",
    type: "standard",
    launcherTitle: "Support",
    showPopoutButton: true,
    darkMode: "auto"
  };
  (function(d,t) {
    var BASE_URL="{{base_url}}";
    var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
    g.src=BASE_URL+"/packs/js/sdk.js";
    g.defer = true;
    g.async = true;
    s.parentNode.insertBefore(g,s);
    g.onload=function(){
      window.chatwootSDK.run({
        websiteToken: "{{website_token}}",
        baseUrl: BASE_URL
      });
    }
  })(document,"script");
  window.addEventListener("chatwoot:ready", function () {
    if (window.chatwootUserData && window.$chatwoot) {
      window.$chatwoot.setUser(window.chatwootUserData.identifier, window.chatwootUserData);
    }
  });
</script>';

        $chatwoot = \App\Models\Extension::where('act', 'chatwoot')->first();
        if (!$chatwoot) {
            $chatwoot = new \App\Models\Extension();
            $chatwoot->act = 'chatwoot';
            $chatwoot->name = 'Chatwoot Live Chat';
            $chatwoot->description = 'Chatwoot is an open-source customer engagement suite, providing real-time live chat alternative to Tawk.to and Zendesk.';
            $chatwoot->image = 'chatwoot.png';
            $chatwoot->script = $chatwootScript;
            $chatwoot->shortcode = [
                'base_url' => [
                    'title' => 'Chatwoot Base URL (e.g. https://app.chatwoot.com)',
                    'value' => 'https://app.chatwoot.com'
                ],
                'website_token' => [
                    'title' => 'Website Token',
                    'value' => ''
                ]
            ];
            $chatwoot->support = '1. Create a Website channel in your Chatwoot Dashboard.\n2. Copy the Website Token and your Chatwoot Base URL.\n3. Paste them here and enable the extension.';
            $chatwoot->status = 0;
            $chatwoot->save();
        } else {
            $chatwoot->script = $chatwootScript;
            $chatwoot->save();
        }

        // 1. AI Bot Plans Table
        if (!\Illuminate\Support\Facades\Schema::hasTable('ai_bot_plans')) {
            \Illuminate\Support\Facades\Schema::create('ai_bot_plans', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('tagline')->nullable();
                $table->string('strategy_type')->default('scalping'); // scalping, breakout, arbitrage, grid, trend
                $table->decimal('min_investment', 28, 8)->default(100);
                $table->decimal('max_investment', 28, 8)->default(5000);
                $table->decimal('daily_roi_min', 8, 2)->default(1.50);
                $table->decimal('daily_roi_max', 8, 2)->default(3.20);
                $table->decimal('win_rate', 8, 2)->default(96.50);
                $table->string('risk_level')->default('low'); // low, medium, high
                $table->integer('trade_duration_days')->default(30);
                $table->tinyInteger('status')->default(1);
                $table->json('features')->nullable();
                $table->json('trading_pairs')->nullable();
                $table->integer('rank')->default(0);
                $table->timestamps();
            });

            // Seed default AI Bot Plans
            \App\Models\AiBotPlan::create([
                'name' => 'Vinance DeepQuant V4.2',
                'tagline' => 'High-Frequency Neural Scalping Algorithm',
                'strategy_type' => 'scalping',
                'min_investment' => 100,
                'max_investment' => 5000,
                'daily_roi_min' => 1.50,
                'daily_roi_max' => 3.20,
                'win_rate' => 96.80,
                'risk_level' => 'low',
                'trade_duration_days' => 30,
                'status' => 1,
                'rank' => 1,
                'features' => ['Sub-millisecond latency order routing', 'Dynamic slippage & spread optimizer', 'Multi-indicator neural consensus (RSI/MACD)', 'Auto Take-Profit & Stop-Loss protection'],
                'trading_pairs' => ['BTC/USDT', 'ETH/USDT', 'SOL/USDT', 'BNB/USDT']
            ]);

            \App\Models\AiBotPlan::create([
                'name' => 'AlphaMatrix Breakout Pro',
                'tagline' => 'Momentum & Volatility Channel Breakout Bot',
                'strategy_type' => 'breakout',
                'min_investment' => 500,
                'max_investment' => 25000,
                'daily_roi_min' => 2.80,
                'daily_roi_max' => 5.50,
                'win_rate' => 94.20,
                'risk_level' => 'medium',
                'trade_duration_days' => 60,
                'status' => 1,
                'rank' => 2,
                'features' => ['Order book depth imbalance detection', 'Adaptive Bollinger Band breakout execution', 'Trailing stop profit maximizer', 'Institutional volume surge trigger'],
                'trading_pairs' => ['BTC/USDT', 'ETH/USDT', 'SOL/USDT', 'AVAX/USDT', 'XRP/USDT']
            ]);

            \App\Models\AiBotPlan::create([
                'name' => 'Institutional Arbitrage Core',
                'tagline' => 'Cross-Exchange Risk-Free Arbitrage Engine',
                'strategy_type' => 'arbitrage',
                'min_investment' => 1000,
                'max_investment' => 100000,
                'daily_roi_min' => 0.90,
                'daily_roi_max' => 2.10,
                'win_rate' => 99.40,
                'risk_level' => 'low',
                'trade_duration_days' => 90,
                'status' => 1,
                'rank' => 3,
                'features' => ['Zero-directional market risk execution', 'Cross-market orderbook disparity scanner', 'Instant atomic settlement', 'Principal capital capital preservation guarantee'],
                'trading_pairs' => ['BTC/USDT', 'ETH/USDT', 'USDC/USDT']
            ]);

            \App\Models\AiBotPlan::create([
                'name' => 'Titan Smart Grid Matrix',
                'tagline' => 'AI Mean-Reversion Automated Geometric Grid',
                'strategy_type' => 'grid',
                'min_investment' => 250,
                'max_investment' => 10000,
                'daily_roi_min' => 2.10,
                'daily_roi_max' => 4.20,
                'win_rate' => 95.60,
                'risk_level' => 'medium',
                'trade_duration_days' => 45,
                'status' => 1,
                'rank' => 4,
                'features' => ['Automated 24/7 limit order buy-low/sell-high matrix', 'Volatility-adjusted grid density', 'Dynamic geometric profit compounding', 'Backtested on 5+ years of market data'],
                'trading_pairs' => ['BTC/USDT', 'ETH/USDT', 'BNB/USDT', 'SOL/USDT', 'DOGE/USDT']
            ]);
        } else {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('ai_bot_plans', 'is_copy_trader')) {
                \Illuminate\Support\Facades\Schema::table('ai_bot_plans', function ($table) {
                    $table->tinyInteger('is_copy_trader')->default(0);
                });
            }
        }

        // 2. User AI Bots Table
        if (!\Illuminate\Support\Facades\Schema::hasTable('user_ai_bots')) {
            \Illuminate\Support\Facades\Schema::create('user_ai_bots', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('bot_plan_id');
                $table->decimal('allocated_amount', 28, 8)->default(0);
                $table->decimal('trailing_stop_loss', 8, 2)->default(2.0);
                $table->decimal('take_profit_target', 8, 2)->default(5.0);
                $table->decimal('current_profit', 28, 8)->default(0);
                $table->tinyInteger('auto_compound')->default(0);
                $table->integer('total_trades')->default(0);
                $table->tinyInteger('status')->default(1); // 1=active, 0=paused, 2=completed
                $table->timestamp('started_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        } else {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_ai_bots', 'trailing_stop_loss')) {
                \Illuminate\Support\Facades\Schema::table('user_ai_bots', function ($table) {
                    $table->decimal('trailing_stop_loss', 8, 2)->default(2.0)->after('allocated_amount');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_ai_bots', 'take_profit_target')) {
                \Illuminate\Support\Facades\Schema::table('user_ai_bots', function ($table) {
                    $table->decimal('take_profit_target', 8, 2)->default(5.0)->after('trailing_stop_loss');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('user_ai_bots', 'auto_compound')) {
                \Illuminate\Support\Facades\Schema::table('user_ai_bots', function ($table) {
                    $table->tinyInteger('auto_compound')->default(0)->after('current_profit');
                });
            }
        }

        // 3. User AI Settings Table (User-Specific Overrides)
        if (!\Illuminate\Support\Facades\Schema::hasTable('user_ai_settings')) {
            \Illuminate\Support\Facades\Schema::create('user_ai_settings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->decimal('custom_win_rate', 8, 2)->nullable();
                $table->decimal('custom_daily_roi_min', 8, 2)->nullable();
                $table->decimal('custom_daily_roi_max', 8, 2)->nullable();
                $table->tinyInteger('force_status')->nullable(); // null=default, 1=force_enable, 0=force_disable
                $table->text('custom_notes')->nullable();
                $table->timestamps();
            });
        }

        // 4. AI Trade Logs Table
        if (!\Illuminate\Support\Facades\Schema::hasTable('ai_trade_logs')) {
            \Illuminate\Support\Facades\Schema::create('ai_trade_logs', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('user_ai_bot_id')->nullable();
                $table->string('pair_symbol')->default('BTC/USDT');
                $table->string('side')->default('BUY'); // BUY, SELL
                $table->decimal('entry_price', 28, 8)->default(0);
                $table->decimal('exit_price', 28, 8)->default(0);
                $table->decimal('amount', 28, 8)->default(0);
                $table->decimal('profit_amount', 28, 8)->default(0);
                $table->decimal('profit_percentage', 8, 2)->default(0);
                $table->string('status')->default('closed'); // open, closed
                $table->timestamps();
            });
        }

        // 5. Staking Tables Schema & Default Pools
        if (!\Illuminate\Support\Facades\Schema::hasTable('staking_pools')) {
            \Illuminate\Support\Facades\Schema::create('staking_pools', function ($table) {
                $table->id();
                $table->unsignedBigInteger('configuration_id')->nullable();
                $table->string('name');
                $table->string('token_symbol')->default('USDT');
                $table->string('type')->default('locked'); // flexible, locked
                $table->integer('lock_period_days')->default(30);
                $table->decimal('apy_rate', 8, 2)->default(12.00);
                $table->decimal('min_amount', 28, 8)->default(100);
                $table->decimal('max_amount', 28, 8)->default(100000);
                $table->decimal('early_unstake_penalty_percentage', 8, 2)->default(0);
                $table->string('badge_tag')->nullable();
                $table->decimal('total_staked', 28, 8)->default(0);
                $table->integer('total_stakers')->default(0);
                $table->integer('rank')->default(0);
                $table->tinyInteger('is_active')->default(1);
                $table->timestamps();
            });
        } else {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('staking_pools', 'name')) {
                \Illuminate\Support\Facades\Schema::table('staking_pools', function ($table) {
                    $table->string('name')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('staking_pools', 'token_symbol')) {
                \Illuminate\Support\Facades\Schema::table('staking_pools', function ($table) {
                    $table->string('token_symbol')->default('USDT');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('staking_pools', 'min_amount')) {
                \Illuminate\Support\Facades\Schema::table('staking_pools', function ($table) {
                    $table->decimal('min_amount', 28, 8)->default(100);
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('staking_pools', 'max_amount')) {
                \Illuminate\Support\Facades\Schema::table('staking_pools', function ($table) {
                    $table->decimal('max_amount', 28, 8)->default(100000);
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('staking_pools', 'early_unstake_penalty_percentage')) {
                \Illuminate\Support\Facades\Schema::table('staking_pools', function ($table) {
                    $table->decimal('early_unstake_penalty_percentage', 8, 2)->default(0);
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('staking_pools', 'badge_tag')) {
                \Illuminate\Support\Facades\Schema::table('staking_pools', function ($table) {
                    $table->string('badge_tag')->nullable();
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('staking_pools', 'rank')) {
                \Illuminate\Support\Facades\Schema::table('staking_pools', function ($table) {
                    $table->integer('rank')->default(0);
                });
            }
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('stakes')) {
            \Illuminate\Support\Facades\Schema::create('stakes', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('pool_id');
                $table->decimal('principal_amount', 28, 8)->default(0);
                $table->decimal('current_amount', 28, 8)->default(0);
                $table->decimal('accumulated_rewards', 28, 8)->default(0);
                $table->timestamp('start_time')->nullable();
                $table->timestamp('end_time')->nullable();
                $table->timestamp('last_compound_time')->nullable();
                $table->boolean('is_compound')->default(false);
                $table->string('status')->default('active'); // active, completed, unstaked
                $table->timestamps();
            });
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('user_staking_settings')) {
            \Illuminate\Support\Facades\Schema::create('user_staking_settings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->decimal('custom_apy_boost', 8, 2)->nullable();
                $table->boolean('force_lock_exemption')->default(false);
                $table->text('custom_notes')->nullable();
                $table->timestamps();
            });
        }

        // Seed Staking Pools if empty
        if (\App\Models\StakingPool::count() == 0) {
            \App\Models\StakingPool::create([
                'name' => 'USDT Flexible High-Yield Earn',
                'token_symbol' => 'USDT',
                'type' => 'flexible',
                'lock_period_days' => 0,
                'apy_rate' => 8.50,
                'min_amount' => 50,
                'max_amount' => 100000,
                'early_unstake_penalty_percentage' => 0,
                'badge_tag' => 'FLEXIBLE',
                'rank' => 1,
                'is_active' => 1
            ]);

            \App\Models\StakingPool::create([
                'name' => 'USDT 30-Day Guaranteed Vault',
                'token_symbol' => 'USDT',
                'type' => 'locked',
                'lock_period_days' => 30,
                'apy_rate' => 12.80,
                'min_amount' => 100,
                'max_amount' => 250000,
                'early_unstake_penalty_percentage' => 1.50,
                'badge_tag' => 'POPULAR',
                'rank' => 2,
                'is_active' => 1
            ]);

            \App\Models\StakingPool::create([
                'name' => 'USDT 90-Day Alpha Maximizer',
                'token_symbol' => 'USDT',
                'type' => 'locked',
                'lock_period_days' => 90,
                'apy_rate' => 18.50,
                'min_amount' => 250,
                'max_amount' => 500000,
                'early_unstake_penalty_percentage' => 2.50,
                'badge_tag' => 'HIGH YIELD',
                'rank' => 3,
                'is_active' => 1
            ]);

            \App\Models\StakingPool::create([
                'name' => 'USDT 180-Day Institutional VIP',
                'token_symbol' => 'USDT',
                'type' => 'locked',
                'lock_period_days' => 180,
                'apy_rate' => 24.20,
                'min_amount' => 500,
                'max_amount' => 1000000,
                'early_unstake_penalty_percentage' => 3.00,
                'badge_tag' => 'INSTITUTIONAL',
                'rank' => 4,
                'is_active' => 1
            ]);
        }

        // 6. User Swap Settings Table
        if (!\Illuminate\Support\Facades\Schema::hasTable('user_swap_settings')) {
            \Illuminate\Support\Facades\Schema::create('user_swap_settings', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->decimal('custom_fee_percentage', 8, 2)->nullable();
                $table->boolean('is_swap_locked')->default(false);
                $table->text('custom_notes')->nullable();
                $table->timestamps();
            });
        }
    } catch (\Exception $e) {
        return "Migration Error: " . $e->getMessage();
    }

    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'System Optimized and Migrations Ran Successfully.';
});

Route::get('cron', 'CronController@cron')->name('cron');

// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});

Route::get('app/deposit/confirm/{hash}', 'Gateway\PaymentController@appDepositConfirm')->name('deposit.app.confirm');

Route::controller("BinaryTradeController")->prefix('binary')->group(function () {
    Route::get('trade/{id?}', 'binary')->name('binary');
    Route::get('trade/tab/close/{id?}/{first_coin_id?}', 'tradeTabClose')->name('binary.trade.tab.close');
    Route::get('trade/tab/add/{id?}', 'tradeTabAdd')->name('binary.trade.tab.add');
    Route::get('trade/tab/update/{id?}', 'tradeTabUpdate')->name('binary.trade.tab.update');
});

Route::controller("TradeController")->prefix('trade')->group(function () {
    Route::get('/order/book/{symbol}', 'orderBook')->name('trade.order.book');
    Route::get('pairs', 'pairs')->name('trade.pairs');
    Route::get('history/{symbol}', 'history')->name('trade.history');
    Route::get('order/list/{pairSym}', 'orderList')->name('trade.order.list');
    Route::get('/{symbol?}', 'trade')->name('trade');
});

Route::middleware(['auth'])->group(function() {
    Route::controller(AITraderController::class)->group(function() {
        Route::get('ai-trader', 'index')->name('user.ai.trader');
        Route::post('ai-trader/start', 'startBot')->name('user.ai.bot.start');
        Route::post('ai-trader/stop/{id}', 'stopBot')->name('user.ai.bot.stop');
        Route::post('ai-trader/harvest/{id}', 'harvestProfit')->name('user.ai.bot.harvest');
        Route::post('ai-trader/harvest-all', 'harvestAllProfits')->name('user.ai.bot.harvest.all');
        Route::post('ai-trader/auto-compound/{id}', 'toggleAutoCompound')->name('user.ai.bot.autocompound');
        Route::get('ai-settings', 'settings')->name('user.ai.settings');
        Route::post('ai-settings/save', 'saveSettings')->name('user.ai.settings.save');
    });

    // Staking & Earn Routes
    Route::controller(StakingController::class)->prefix('user/staking')->name('user.staking.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('stake', 'stake')->name('stake');
        Route::post('harvest/{id}', 'harvest')->name('harvest');
        Route::post('unstake/{id}', 'unstake')->name('unstake');
        Route::post('auto-compound/{id}', 'toggleAutoCompound')->name('autocompound');
    });
    Route::get('staking', [StakingController::class, 'index'])->name('user.staking');
    Route::get('user/staking', [StakingController::class, 'index'])->name('user.staking.index');
});

Route::namespace('P2P')->group(function () {
    Route::controller("HomeController")->prefix('p2p')->group(function () {
        Route::get("/advertiser/{username}", 'advertiser')->name('p2p.advertiser');
        Route::get("/{type?}/{coin?}/{currency?}/{paymentMethod?}/{region?}/{amount?}", 'p2p')->name('p2p');
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::name('user.')->prefix('user')->group(function () {
        Route::get('coin-swap', [CoinSwapController::class, 'index'])->name('coin.swap');
        Route::post('coin-swap', [CoinSwapController::class, 'swap'])->name('coin.swap');
        Route::post('coin-swap/calculate', [CoinSwapController::class, 'calculate'])->name('coin.swap.calculate');
    });
});

// Cron Routes
Route::get('/cron/process-staking-rewards', [CronController::class, 'processStakingRewards'])
    ->name('cron.staking.rewards')
    ->middleware('signed');

Route::get('/js/staking.js', function() {
    return response()->file(resource_path('views/templates/basic/user/staking/js/staking.js'));
})->name('staking.js');

Route::controller('SiteController')->group(function () {
    Route::get('/pwa/configuration', 'pwaConfiguration')->name('pwa.configuration');
    Route::get('/market/list', 'marketList')->name('market.list');
    Route::get('/crypto/list', 'cryptoCurrencyList')->name('crypto_currency.list');
    Route::get('/market', 'market')->name('market');
    Route::post('/subscribe', 'subscribe')->name('subscribe');
    Route::get('/crypto-currency', 'crypto')->name('crypto_currencies');
    Route::get('/crypto/currency/{symbol}', 'cryptoCurrencyDetails')->name('crypto.details');
    Route::get('/about-us', 'about')->name('about');
    Route::post('pusher/auth/{socketId}/{channelName}', "pusherAuthentication");

    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');
    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');
    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');
    Route::get('blog/{slug}', 'blogDetails')->name('blog.details');
    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');
    Route::get('placeholder-image/{size}', 'placeholderImage')->name('placeholder.image')->withoutMiddleware('maintenance');
    Route::get('maintenance-mode', 'maintenance')->withoutMiddleware('maintenance')->name('maintenance');

    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home');
});

Route::post('/user/p2p/advertisement/save/{id?}', [AdvertisementController::class, 'save'])
    ->name('user.p2p.advertisement.save');
    
Route::get('/user/p2p/advertisement/create/{id?}', [AdvertisementController::class, 'create'])
    ->name('user.p2p.advertisement.create');
    
   Route::get('/ai-trader/documentation', function () {
    return view('Template::user.ai_trader_docs', [
        'pageTitle' => 'AI Trader Documentation'
    ]);
})->name('user.documentation');;


Route::post('/telegram-webhook', function(Request $request) {
    $update = $request->all();
    
    if (isset($update['message']['text']) && str_starts_with($update['message']['text'], '/start')) {
        $userId = str_replace('/start ', '', $update['message']['text']);
        $telegramUser = $update['message']['from'];
        
        DB::table('telegram_activations')->updateOrInsert(
            ['user_id' => $userId],
            [
                'telegram_username' => $telegramUser['username'] ?? null,
                'created_at' => now()
            ]
        );
    }
    
    return response()->json(['ok' => true]);
});

// Correct login route for your template structure
Route::get('/user/login', function() {
    return view('templates.basic.user.auth.login', [
        'pageTitle' => 'User Login' // Add this line
    ]);
})->name('user.login')->middleware('guest');




Route::get('/admin/telegram-activations', [TelegramActivationController::class, 'index'])
    ->name('admin.telegram-activations.index');


Route::prefix('admin')->group(function() {
    // Telegram Activations
    Route::get('telegram-activations', [TelegramActivationController::class, 'index'])->name('admin.telegram-activations.index');
    Route::post('telegram-activations/approve/{id}', [TelegramActivationController::class, 'approve'])->name('admin.telegram-activations.approve');
    Route::post('telegram-activations/reject/{id}', [TelegramActivationController::class, 'reject'])->name('admin.telegram-activations.reject');
    Route::get('telegram-activations/details/{id}', [TelegramActivationController::class, 'details'])->name('admin.telegram-activations.details');
});


// Internal route for cron job - not meant to be accessed directly
Route::get('/update-rewards-internal', function () {
    // Only allow this route to be called from our server
    if (!app()->runningInConsole() && request()->server('REMOTE_ADDR') !== request()->server('SERVER_ADDR')) {
        abort(403, 'Access denied');
    }
    
    // Create and call the controller method
    $controller = new \App\Http\Controllers\User\StakingController();
    return $controller->updateRewards();
});