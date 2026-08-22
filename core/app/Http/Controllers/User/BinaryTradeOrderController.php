<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\BinaryTrade;
use App\Models\CoinPair;
use App\Models\Transaction;
use App\Models\Notification;
use App\Mail\TradeNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Pusher\Pusher;

class BinaryTradeOrderController extends Controller
{
    /**
     * Send notification to Telegram
     * 
     * @param string $message The message to send
     * @return void
     */
    protected function sendTelegramNotification($message)
    {
        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_CHAT_ID');
            
            if (!$token || !$chatId) {
                Log::error('Telegram notification failed: Bot token or Chat ID not found in .env file');
                return;
            }
            
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $params = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                Log::error('Telegram notification failed: ' . curl_error($ch));
            }
            
            curl_close($ch);
            
            Log::info('Telegram notification sent: ' . $message);
            Log::debug('Telegram API response: ' . $response);
        } catch (\Exception $e) {
            Log::error('Telegram notification exception: ' . $e->getMessage());
        }
    }

    protected function sendTradeNotification($user, $title, $message, $type = 'info')
    {
       try {
            // Create and save the notification
            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = $title;
            $notification->message = $message;
            $notification->type = $type;
            $notification->save();

            // Broadcast the notification using Pusher
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true
                ]
            );

            $pusher->trigger(
                'user.' . $user->id,
                'trade-notification',
                [
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'created_at' => now()->toDateTimeString()
                ]
            );

            Log::info("Notification sent to user {$user->id}: {$title} - {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send notification: " . $e->getMessage());
        }
    }

    protected function sendTradeEmail($user, $trade, $type)
    {
        $data = [
            'user' => $user,
            'trade' => $trade,
            'type' => $type,
            'entry_price' => $trade->last_price,
            'exit_price' => $trade->result_price ?? null,
            'amount' => $trade->amount,
            'duration' => $trade->duration,
            'direction' => $trade->direction,
            'symbol' => $trade->coinPair->coin->symbol,
            'timestamp' => $trade->created_at,
            'trade_id' => $trade->id,
            'trx' => $trade->trx
        ];

        Mail::to($user->email)->send(new TradeNotification($data));
    }

    public function binaryTradeOrder(Request $request)
    {
        $coinPair = CoinPair::active()->activeMarket()->activeCoin()->where(function ($query) {
            $query->where('type', Status::BINARY_TRADE)->orWhere('type', Status::BOTH_TRADE);
        })->with(['coin', 'market', 'marketData'])->where('id', $request->coin_pair_id)->first();

        if (!$coinPair) {
            return response()->json(['error' => 'Coin Pair not found.']);
        }

        $minInvest = $coinPair->min_binary_trade_amount;
        $maxInvest = $coinPair->max_binary_trade_amount;
        $duration  = implode(',', $coinPair->binary_trade_duration);

        $validator = Validator::make($request->all(), [
            'amount'       => "required|numeric|gte:$minInvest|lte:$maxInvest",
            'duration'     => "required|in:$duration",
            'direction'    => 'required|string|in:higher,lower',
            'coin_pair_id' => "required|integer|exists:coin_pairs,id",
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $user       = auth()->user();
        $existTrade = BinaryTrade::where('user_id', $user->id)->inactive()->exists();
        if ($existTrade) {
            return response()->json(['error' => 'You need to wait until the ongoing trade is completed']);
        }

        $userWallet = $user->wallets()->where('wallet_type', Status::WALLET_TYPE_FUNDING)->where('currency_id', $coinPair->coin_id)->first();
        if (!$userWallet) {
            return response()->json(['error' => 'You have no ' . @$coinPair->coin->symbol . ' funding wallet']);
        }

        if ($request->amount > $userWallet->balance) {
            return response()->json(['error' => 'Insufficient balance in your ' . @$coinPair->coin->symbol . ' funding wallet']);
        }

        $symbol   = str_replace('_', '-', @$coinPair->symbol);
        $url      = 'https://api.kucoin.com/api/v1/market/orderbook/level1?symbol=' . $symbol;
        $response = CurlRequest::curlContent($url);
        $response = json_decode($response);

        if (!isset($response->data) || !isset($response->data->price)) {
            Log::error('KuCoin API response error: ' . json_encode($response));
            return response()->json(['error' => 'Failed to fetch current price from exchange']);
        }

        $currentPrice = (float) $response->data->price;
        Log::info("Trade entry price for {$symbol}: {$currentPrice}");

        $userWallet->balance -= $request->amount;
        $userWallet->save();

        $trx = getTrx();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->wallet_id    = $userWallet->id;
        $transaction->amount       = $request->amount;
        $transaction->charge       = 0;
        $transaction->post_balance = $userWallet->balance;
        $transaction->trx          = $trx;
        $transaction->trx_type     = '-';
        $transaction->details      = $request->amount . ' ' . @$coinPair->coin->symbol . ' ' . 'binary trade order';
        $transaction->remark       = 'binary_trade';
        $transaction->save();

        $currency       = $coinPair->coin;
        $currency->rate = $currentPrice;
        $currency->save();

        $binaryTrade                 = new BinaryTrade();
        $binaryTrade->user_id        = $user->id;
        $binaryTrade->coin_pair_id   = $request->coin_pair_id;
        $binaryTrade->amount         = $request->amount;
        $binaryTrade->last_price     = $currentPrice;
        $binaryTrade->duration       = (int) $request->duration;
        $binaryTrade->direction      = $request->direction;
        $binaryTrade->trx            = $trx;
        $binaryTrade->trade_ended_at = Carbon::now()->addSeconds((int) $request->duration);
        $binaryTrade->save();

        // Send in-app notification for new trade
        $this->sendTradeNotification(
            $user,
            'New Trade Opened',
            "You have opened a new trade for {$request->amount} {$coinPair->coin->symbol} with direction: {$request->direction}",
            'success'
        );

        // Send email notification for new trade
        $this->sendTradeEmail($user, $binaryTrade, 'opened');

        // Send Telegram notification for new trade order
        $notificationMessage = "🆕 <b>NEW BINARY TRADE ORDER</b>\n" .
            "👤 User: " . $user->username . "\n" .
            "💰 Amount: " . $request->amount . " " . @$coinPair->coin->symbol . "\n" .
            "📈 Direction: " . ucfirst($request->direction) . "\n" .
            "⏱️ Duration: " . $request->duration . " seconds\n" .
            "💹 Entry Price: " . $currentPrice . "\n" .
            "🔢 Trade ID: " . $binaryTrade->id . "\n" .
            "📝 TRX: " . $trx . "\n" .
            "⌚ Time: " . Carbon::now()->format('Y-m-d H:i:s');
            
        $this->sendTelegramNotification($notificationMessage);

        $newTrade = view('Template::partials.single_binary_table', compact('binaryTrade'))->render();

        return response()->json([
            'binary_trade_id' => $binaryTrade->id,
            'amount'          => $binaryTrade->amount,
            'direction'       => $binaryTrade->direction,
            'duration'        => $binaryTrade->duration,
            'newTrade'        => $newTrade,
        ]);
    }

    public function binaryTradeComplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'binary_trade_id' => "required|integer|exists:binary_trades,id",
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        try {
            // Get the trade with proper relationships
            $binaryTrade = BinaryTrade::with('coinPair.coin')
                ->inactive()
                ->pending()
                ->where('user_id', auth()->id())
                ->where('id', $request->binary_trade_id)
                ->firstOrFail();

            // Parse end time and validate
            $tradeEndedAt = Carbon::parse($binaryTrade->trade_ended_at);
            if ($tradeEndedAt->isFuture()) {
                return response()->json(['error' => 'Trade is not yet complete']);
            }

            // Prepare symbol for API request
            $symbol = str_replace('_', '-', $binaryTrade->coinPair->symbol);
            
            // Get the exit price with multiple attempts
            $exitPrice = null;
            $attempts = 0;
            $maxAttempts = 3;
            
            while ($attempts < $maxAttempts && is_null($exitPrice)) {
                // First try to get historical price
                $exitPrice = $this->getClosingPriceAtTime($symbol, $tradeEndedAt);
                
                if (is_null($exitPrice)) {
                    // If historical price fails, try current price
                    $exitPrice = $this->getCurrentPrice($symbol);
                }
                
                if (is_null($exitPrice)) {
                    $attempts++;
                    sleep(1); // Wait 1 second before retrying
                }
            }

            if (is_null($exitPrice)) {
                throw new \Exception('Could not retrieve price data after multiple attempts');
            }

            // Ensure exit price is different from entry price
            if ((float)$exitPrice === (float)$binaryTrade->last_price) {
                // If prices are the same, try to get a new price
                $newPrice = $this->getCurrentPrice($symbol);
                if (!is_null($newPrice) && (float)$newPrice !== (float)$binaryTrade->last_price) {
                    $exitPrice = $newPrice;
                } else {
                    // If still the same, add a small random variation (0.1% of the price)
                    $variation = (float)$binaryTrade->last_price * 0.001;
                    $exitPrice = (float)$binaryTrade->last_price + (rand(0, 1) ? $variation : -$variation);
                }
            }

            // Log prices for debugging
            Log::info("Trade {$binaryTrade->id} - Entry Price: {$binaryTrade->last_price}, Exit Price: {$exitPrice}");

            // Update trade status and process result
            DB::transaction(function () use ($binaryTrade, $exitPrice) {
                $binaryTrade->status = Status::ENABLE;
                $binaryTrade->result_price = $exitPrice;
                $binaryTrade->save();
                
                // Update currency rate
                if ($binaryTrade->coinPair->coin) {
                    $binaryTrade->coinPair->coin->rate = $exitPrice;
                    $binaryTrade->coinPair->coin->save();
                }
                
                // Process win/loss
                $notification = $this->binaryTradeWinLoss($binaryTrade, $exitPrice);
            });

            $trades = BinaryTrade::where('user_id', auth()->id())
                ->with('coinPair')
                ->active()
                ->latest()
                ->take(5)
                ->get();
                
            $closedTradeTable = view('Template::partials.binary_table', compact('trades'))->render();
            
            return response()->json([
                'success' => true,
                'win_status' => $binaryTrade->win_status,
                'notification' => $notification ?? null,
                'closedTradeTable' => $closedTradeTable,
            ]);

        } catch (\Exception $e) {
            Log::error("Trade completion failed for trade {$request->binary_trade_id}: " . $e->getMessage());
            return response()->json(['error' => 'Trade processing failed: ' . $e->getMessage()]);
        }
    }

    protected function getClosingPriceAtTime($symbol, Carbon $time)
    {
        try {
            $endTime = $time->timestamp;
            $startTime = $time->copy()->subMinute()->timestamp;
            
            $url = "https://api.kucoin.com/api/v1/market/candles?symbol=$symbol&type=1min&startAt=$startTime&endAt=$endTime&reverse=true";
            $response = json_decode(CurlRequest::curlContent($url), true);
            
            if (!empty($response['data'])) {
                return $response['data'][0][2]; // Close price
            }
            return null;
        } catch (\Exception $e) {
            Log::error("Failed to get historical price: " . $e->getMessage());
            return null;
        }
    }

    protected function getCurrentPrice($symbol)
    {
        try {
            $url = "https://api.kucoin.com/api/v1/market/orderbook/level1?symbol=$symbol";
            $response = json_decode(CurlRequest::curlContent($url), true);
            return $response['data']['price'] ?? null;
        } catch (\Exception $e) {
            Log::error("Failed to get current price: " . $e->getMessage());
            return null;
        }
    }

    public function binaryTradeWinLoss($binaryTrade, $currentPrice)
    {
        $user = auth()->user();
        $currencySymbol = $binaryTrade->coinPair->coin->symbol;

        // Convert prices to float for accurate comparison
        $entryPrice = (float) $binaryTrade->last_price;
        $exitPrice = (float) $currentPrice;

        Log::info("Trade {$binaryTrade->id} - Comparing prices: Entry={$entryPrice}, Exit={$exitPrice}, Direction={$binaryTrade->direction}");

        if (($binaryTrade->direction == "higher" && $exitPrice > $entryPrice) || 
            ($binaryTrade->direction == "lower" && $exitPrice < $entryPrice)) {
            $binaryTrade->win_status = Status::BINARY_TRADE_WIN;
            $binaryTrade->win_amount = $binaryTrade->amount + ($binaryTrade->amount * $binaryTrade->coinPair->binary_trade_profit / 100);
            $notification = 'Congratulations! You have Won ' . $binaryTrade->win_amount . ' ' . $currencySymbol . ' from binary trade, with an Exit Price of ' . $exitPrice;
            
            // Send in-app notification for win
            $this->sendTradeNotification(
                $user,
                'Trade Won',
                "Congratulations! You have won {$binaryTrade->win_amount} {$currencySymbol} from your trade",
                'success'
            );

            // Send email notification for win
            $this->sendTradeEmail($user, $binaryTrade, 'won');

            // Send Telegram notification for WIN
            $notificationMessage = "✅ <b>BINARY TRADE WIN</b>\n" .
                "👤 User: " . $user->username . "\n" .
                "💰 Amount: " . $binaryTrade->amount . " " . $currencySymbol . "\n" .
                "💲 Win Amount: " . $binaryTrade->win_amount . " " . $currencySymbol . "\n" .
                "📈 Direction: " . ucfirst($binaryTrade->direction) . "\n" .
                "💹 Entry Price: " . $entryPrice . "\n" .
                "💹 Exit Price: " . $exitPrice . "\n" .
                "⏱️ Duration: " . $binaryTrade->duration . " seconds\n" .
                "🔢 Trade ID: " . $binaryTrade->id . "\n" .
                "📝 TRX: " . $binaryTrade->trx . "\n" .
                "⌚ Time: " . Carbon::now()->format('Y-m-d H:i:s');
                
            $this->sendTelegramNotification($notificationMessage);
        } else {
            $binaryTrade->win_status = Status::BINARY_TRADE_LOSE;
            $notification = 'You lost ' . $binaryTrade->amount . ' ' . $currencySymbol . ', with an Exit Price of ' . $exitPrice;
            
            // Send in-app notification for loss
            $this->sendTradeNotification(
                $user,
                'Trade Lost',
                "Your trade has ended with a loss of {$binaryTrade->amount} {$currencySymbol}",
                'error'
            );

            // Send email notification for loss
            $this->sendTradeEmail($user, $binaryTrade, 'lost');

            // Send Telegram notification for LOSS
            $notificationMessage = "❌ <b>BINARY TRADE LOSS</b>\n" .
                "👤 User: " . $user->username . "\n" .
                "💰 Lost Amount: " . $binaryTrade->amount . " " . $currencySymbol . "\n" .
                "📈 Direction: " . ucfirst($binaryTrade->direction) . "\n" .
                "💹 Entry Price: " . $entryPrice . "\n" .
                "💹 Exit Price: " . $exitPrice . "\n" .
                "⏱️ Duration: " . $binaryTrade->duration . " seconds\n" .
                "🔢 Trade ID: " . $binaryTrade->id . "\n" .
                "📝 TRX: " . $binaryTrade->trx . "\n" .
                "⌚ Time: " . Carbon::now()->format('Y-m-d H:i:s');
                
            $this->sendTelegramNotification($notificationMessage);
        }

        $binaryTrade->result_price = $exitPrice;
        $binaryTrade->profit = $binaryTrade->coinPair->binary_trade_profit;
        $binaryTrade->status = Status::ENABLE;
        $binaryTrade->save();

        if ($binaryTrade->win_status == Status::BINARY_TRADE_WIN) {
            $userWallet = $user->wallets()->where('wallet_type', Status::WALLET_TYPE_FUNDING)->where('currency_id', $binaryTrade->coinPair->coin_id)->first();
            $userWallet->balance += $binaryTrade->win_amount;
            $userWallet->save();

            $transaction               = new Transaction();
            $transaction->user_id      = $user->id;
            $transaction->wallet_id    = $userWallet->id;
            $transaction->amount       = $binaryTrade->win_amount;
            $transaction->charge       = 0;
            $transaction->post_balance = $userWallet->balance;
            $transaction->trx          = getTrx();
            $transaction->trx_type     = '+';
            $transaction->details      = $binaryTrade->win_amount . ' ' . $currencySymbol . ' binary trade win';
            $transaction->remark       = 'binary_trade';
            $transaction->save();
            
// Send in-app notification for balance update 
$this->sendTradeNotification(
    $user,
    'Balance Update',
    "Your {$currencySymbol} wallet balance has been updated to " . number_format($userWallet->balance, 8) . " {$currencySymbol}",
    'info'  // Changed back to 'info' as it's an informational update
);
            
            // Send Telegram notification for wallet update after win
            $notificationMessage = "💵 <b>WALLET UPDATED (TRADE WIN)</b>\n" .
                "👤 User ID: " . $user->id . "\n" .
                "💰 Amount: " . $binaryTrade->win_amount . " " . $currencySymbol . "\n" .
                "💼 New Balance: " . $userWallet->balance . " " . $currencySymbol . "\n" .
                "📝 TRX: " . $transaction->trx . "\n" .
                "⌚ Time: " . Carbon::now()->format('Y-m-d H:i:s');
                
            $this->sendTelegramNotification($notificationMessage);
        }
        return $notification;
    }

    public function allTrade()
    {
        $pageTitle = 'All Binary Trade';
        $trades    = $this->getBinaryTrade('');
        return view('Template::user.binary.trade_history', compact('pageTitle', 'trades'));
    }

    public function winTrade()
    {
        $pageTitle = 'Win Binary Trade';
        $trades    = $this->getBinaryTrade('win');
        return view('Template::user.binary.trade_history', compact('pageTitle', 'trades'));
    }

    public function loseTrade()
    {
        $pageTitle = 'Lose Binary Trade';
        $trades    = $this->getBinaryTrade('lose');
        return view('Template::user.binary.trade_history', compact('pageTitle', 'trades'));
    }

    public function refundTrade()
    {
        $pageTitle = 'Refund Binary Trade';
        $trades    = $this->getBinaryTrade('refund');
        return view('Template::user.binary.trade_history', compact('pageTitle', 'trades'));
    }

    protected function getBinaryTrade($scope)
    {
        if ($scope) {
            $trades = BinaryTrade::$scope();
        } else {
            $trades = BinaryTrade::query();
        }
        return $trades->where('user_id', auth()->id())->searchable(['trx', 'coinPair:symbol', 'coinPair.coin:symbol'])->with('coinPair')->orderBy('id', 'desc')->paginate(getPaginate());
    }

    public function tradeHistory(Request $request)
    {
        $page   = $request->page ?? 1;
        $trades = BinaryTrade::active()->where('user_id', auth()->id())->with('coinPair')->orderBy('id', 'desc')->skip(($page - 1) * 5)->take(5)->get();

        $view = '';
        foreach ($trades as $key => $binaryTrade) {
            $view .= view('Template::partials.single_binary_table', compact('binaryTrade'))->render();
        }

        return response()->json([
            'trades' => $view,
        ]);
    }
} 