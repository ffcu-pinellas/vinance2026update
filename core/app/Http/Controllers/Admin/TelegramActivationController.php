<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TelegramActivationController extends Controller
{
    public function index()
    {
        $pageTitle = 'Telegram Activations'; // Added page title
        
        $pending = DB::table('telegram_activations')
                   ->join('users', 'users.id', '=', 'telegram_activations.user_id')
                   ->select(
                       'telegram_activations.*',
                       'users.id as user_id',
                       'users.username',
                       'users.email',
                       'users.created_at as user_created_at'
                   )
                   ->where('telegram_activations.status', 0)
                   ->orderBy('telegram_activations.created_at', 'desc')
                   ->paginate(10);

        $active = DB::table('telegram_activations')
                  ->join('users', 'users.id', '=', 'telegram_activations.user_id')
                  ->leftJoin('users as approvers', 'approvers.id', '=', 'telegram_activations.approved_by')
                  ->select(
                      'telegram_activations.*',
                      'users.id as user_id',
                      'users.username',
                      'users.email',
                      'approvers.username as approved_by_name'
                  )
                  ->where('telegram_activations.status', 1)
                  ->orderBy('telegram_activations.activated_at', 'desc')
                  ->paginate(10);

        return view('admin.telegram_activations', compact('pending', 'active', 'pageTitle')); // Added pageTitle to compact
    }

    public function approve($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $activation = DB::table('telegram_activations')->where('id', $id)->first();
                
                if (!$activation) {
                    throw new \Exception('Activation request not found');
                }

                // Update user
                DB::table('users')
                  ->where('id', $activation->user_id)
                  ->update([
                      'telegram_activated' => 1,
                      'telegram_activated_at' => now()
                  ]);
                
                // Update activation record
                DB::table('telegram_activations')
                  ->where('id', $id)
                  ->update([
                      'status' => 1,
                      'activated_at' => now(),
                      'approved_by' => auth()->id(),
                      'updated_at' => now()
                  ]);
            });

            return back()->with('success', 'Activation approved successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($id, $request) {
                $activation = DB::table('telegram_activations')->where('id', $id)->first();
                
                if (!$activation) {
                    throw new \Exception('Activation request not found');
                }

                DB::table('telegram_activations')
                  ->where('id', $id)
                  ->update([
                      'status' => 2, // 2 = rejected
                      'rejection_reason' => $request->rejection_reason,
                      'approved_by' => auth()->id(),
                      'updated_at' => now()
                  ]);
            });

            return back()->with('success', 'Activation rejected successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function details($id)
    {
        $pageTitle = 'Activation Details'; // Added page title
        
        $activation = DB::table('telegram_activations')
                       ->join('users', 'users.id', '=', 'telegram_activations.user_id')
                       ->leftJoin('users as approvers', 'approvers.id', '=', 'telegram_activations.approved_by')
                       ->select(
                           'telegram_activations.*',
                           'users.username',
                           'users.email',
                           'users.created_at as user_created_at',
                           'approvers.username as approved_by_name'
                       )
                       ->where('telegram_activations.id', $id)
                       ->first();

        if (!$activation) {
            return back()->with('error', 'Activation record not found');
        }

        return view('admin.telegram_activation_details', compact('activation', 'pageTitle')); // Added pageTitle to compact
    }
}