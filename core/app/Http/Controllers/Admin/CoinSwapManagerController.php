<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\CoinSwap;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSwapSetting;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoinSwapManagerController extends Controller
{
    public function index()
    {
        $pageTitle = 'Coin Swap & Convert Overview';

        $totalSwapsCount = CoinSwap::count();
        $totalFeesGenerated = CoinSwap::sum('charge');
        $totalUniqueTraders = CoinSwap::distinct('user_id')->count('user_id');

        // Calculate approximate volume in USD (sum of USDT equivalent or from_amount where symbol USDT)
        $totalVolumeUsd = CoinSwap::whereHas('fromCurrency', function($q) {
            $q->where('symbol', 'USDT');
        })->sum('from_amount');

        if ($totalVolumeUsd == 0) {
            $totalVolumeUsd = CoinSwap::sum('from_amount');
        }

        $recentSwaps = CoinSwap::with(['user', 'fromCurrency', 'toCurrency'])
            ->latest()
            ->take(10)
            ->get();

        $currencies = Currency::active()->orderBy('name')->get();

        return view('admin.coin_swap.index', compact(
            'pageTitle',
            'totalSwapsCount',
            'totalFeesGenerated',
            'totalUniqueTraders',
            'totalVolumeUsd',
            'recentSwaps',
            'currencies'
        ));
    }

    public function history(Request $request)
    {
        $pageTitle = 'Swap History & Manual Injector';

        $query = CoinSwap::with(['user', 'fromCurrency', 'toCurrency']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('username', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                })->orWhereHas('fromCurrency', function ($c) use ($search) {
                    $c->where('symbol', 'like', "%$search%");
                })->orWhereHas('toCurrency', function ($c) use ($search) {
                    $c->where('symbol', 'like', "%$search%");
                });
            });
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->from_currency_id) {
            $query->where('from_currency_id', $request->from_currency_id);
        }

        if ($request->to_currency_id) {
            $query->where('to_currency_id', $request->to_currency_id);
        }

        $swaps = $query->latest()->paginate(getPaginate());
        $users = User::active()->orderBy('username')->get();
        $currencies = Currency::active()->orderBy('name')->get();

        return view('admin.coin_swap.history', compact('pageTitle', 'swaps', 'users', 'currencies'));
    }

    public function injectSwap(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id|different:from_currency_id',
            'from_amount' => 'required|numeric|gt:0',
            'to_amount' => 'required|numeric|gt:0',
            'rate' => 'required|numeric|gt:0',
            'charge' => 'nullable|numeric|gte:0',
            'created_at' => 'nullable|date',
            'adjust_balance' => 'nullable|in:0,1'
        ]);

        $user = User::findOrFail($request->user_id);
        $fromCurrency = Currency::findOrFail($request->from_currency_id);
        $toCurrency = Currency::findOrFail($request->to_currency_id);

        $fromAmount = (float)$request->from_amount;
        $toAmount = (float)$request->to_amount;
        $rate = (float)$request->rate;
        $charge = (float)($request->charge ?? 0);
        $createdAt = $request->created_at ? Carbon::parse($request->created_at) : now();

        DB::beginTransaction();
        try {
            if ($request->adjust_balance == 1) {
                // Deduct from wallet
                $fromWallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', $fromCurrency->id)->first();
                if ($fromWallet) {
                    $fromWallet->balance -= $fromAmount;
                    $fromWallet->save();
                }

                // Add to wallet
                $toWallet = Wallet::spot()->firstOrCreate(
                    ['user_id' => $user->id, 'currency_id' => $toCurrency->id],
                    ['balance' => 0]
                );
                $toWallet->balance += $toAmount;
                $toWallet->save();
            }

            $swap = new CoinSwap();
            $swap->user_id = $user->id;
            $swap->from_currency_id = $fromCurrency->id;
            $swap->to_currency_id = $toCurrency->id;
            $swap->from_amount = $fromAmount;
            $swap->to_amount = $toAmount;
            $swap->rate = $rate;
            $swap->charge = $charge;
            $swap->status = Status::COMPLETED;
            $swap->created_at = $createdAt;
            $swap->save();

            DB::commit();

            $notify[] = ['success', "Successfully injected swap for {$user->username}: {$fromAmount} {$fromCurrency->symbol} -> {$toAmount} {$toCurrency->symbol}"];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error', 'Failed to inject swap: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function updateSwap(Request $request, $id)
    {
        $request->validate([
            'from_amount' => 'required|numeric|gt:0',
            'to_amount' => 'required|numeric|gt:0',
            'rate' => 'required|numeric|gt:0',
            'charge' => 'required|numeric|gte:0',
            'created_at' => 'required|date'
        ]);

        $swap = CoinSwap::findOrFail($id);
        $swap->from_amount = (float)$request->from_amount;
        $swap->to_amount = (float)$request->to_amount;
        $swap->rate = (float)$request->rate;
        $swap->charge = (float)$request->charge;
        $swap->created_at = Carbon::parse($request->created_at);
        $swap->save();

        $notify[] = ['success', 'Swap record updated successfully'];
        return back()->withNotify($notify);
    }

    public function revertSwap($id)
    {
        $swap = CoinSwap::with(['user', 'fromCurrency', 'toCurrency'])->findOrFail($id);
        
        if ($swap->status != Status::COMPLETED) {
            $notify[] = ['error', 'This swap is not in completed status or already reverted.'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        try {
            $user = $swap->user;
            
            // Refund from_amount to user's fromWallet
            $fromWallet = Wallet::spot()->firstOrCreate(
                ['user_id' => $user->id, 'currency_id' => $swap->from_currency_id],
                ['balance' => 0]
            );
            $fromWallet->balance += $swap->from_amount;
            $fromWallet->save();

            // Deduct to_amount from user's toWallet
            $toWallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', $swap->to_currency_id)->first();
            if ($toWallet) {
                $toWallet->balance = max(0, $toWallet->balance - $swap->to_amount);
                $toWallet->save();
            }

            $swap->status = Status::REJECTED;
            $swap->save();

            DB::commit();

            $notify[] = ['success', "Swap reverted! Refunded {$swap->from_amount} {$swap->fromCurrency->symbol} to {$user->username}."];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error', 'Failed to revert swap: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function deleteSwap($id)
    {
        $swap = CoinSwap::findOrFail($id);
        $swap->delete();

        $notify[] = ['success', 'Swap record deleted successfully'];
        return back()->withNotify($notify);
    }

    public function userSwapSettings($userId)
    {
        $user = User::findOrFail($userId);
        $pageTitle = 'Coin Swap Settings - ' . $user->username;
        $userSetting = UserSwapSetting::where('user_id', $user->id)->first();
        $userSwaps = CoinSwap::with(['fromCurrency', 'toCurrency'])->where('user_id', $user->id)->latest()->get();

        return view('admin.users.swap_settings', compact('pageTitle', 'user', 'userSetting', 'userSwaps'));
    }

    public function updateUserSwapSettings(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $request->validate([
            'custom_fee_percentage' => 'nullable|numeric|between:0,100',
            'is_swap_locked' => 'nullable|in:0,1',
            'custom_notes' => 'nullable|string'
        ]);

        $setting = UserSwapSetting::firstOrNew(['user_id' => $user->id]);
        $setting->custom_fee_percentage = $request->custom_fee_percentage;
        $setting->is_swap_locked = $request->is_swap_locked ?? 0;
        $setting->custom_notes = $request->custom_notes;
        $setting->save();

        $notify[] = ['success', "Swap settings for {$user->username} updated successfully!"];
        return back()->withNotify($notify);
    }
}
