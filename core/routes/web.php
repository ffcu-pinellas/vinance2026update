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

// Add the AI Auto-Trader route here
Route::get('ai-trader', [UserController::class, 'aiTrader'])->name('user.ai.trader');


Route::namespace('P2P')->group(function () {
    Route::controller("HomeController")->prefix('p2p')->group(function () {
        Route::get("/advertiser/{username}", 'advertiser')->name('p2p.advertiser');
        Route::get("/{type?}/{coin?}/{currency?}/{paymentMethod?}/{region?}/{amount?}", 'p2p')->name('p2p');
    });
});

Route::middleware(['auth'])->group(function() {
    Route::controller(AITraderController::class)->group(function() {
        Route::get('ai-settings', 'settings')->name('user.ai.settings');
        Route::post('ai-settings/save', 'saveSettings')->name('user.ai.settings.save');
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::name('user.')->prefix('user')->group(function () {
        Route::get('coin-swap', [CoinSwapController::class, 'index'])->name('coin.swap');
        Route::post('coin-swap', [CoinSwapController::class, 'swap'])->name('coin.swap');
        Route::post('coin-swap/calculate', [CoinSwapController::class, 'calculate'])->name('coin.swap.calculate');
    });
});


Route::middleware(['auth', 'checkProject'])->group(function () {
    Route::prefix('user')->name('user.')->group(function () {
        Route::controller('User\StakingController')->prefix('staking')->name('staking.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('store', 'store')->name('store');
            Route::post('unstake', 'unstake')->name('unstake');
            Route::post('compound', 'compound')->name('compound');
        });
    });
});

// Cron Routes
Route::get('/cron/process-staking-rewards', [CronController::class, 'processStakingRewards'])
    ->name('cron.staking.rewards')
    ->middleware('signed');
    
    Route::middleware(['auth'])->group(function () {
    Route::get('user/staking', [StakingController::class, 'index'])->name('user.staking.index');
});

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