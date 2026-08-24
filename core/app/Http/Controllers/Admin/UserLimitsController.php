<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\GatewayCurrency;
use App\Models\WithdrawMethod;
use App\Models\UserDepositSetting;
use App\Models\UserWithdrawSetting;
use App\Models\Form;
use Illuminate\Http\Request;

class UserLimitsController extends Controller
{
    public function limitsSettings($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'Custom Limits & Forms - ' . $user->username;
        
        $gatewayCurrencies = GatewayCurrency::with('method')->get();
        $withdrawMethods = WithdrawMethod::get();
        $forms = Form::get();
        
        $userDepositSettings = UserDepositSetting::where('user_id', $id)->get()->keyBy('gateway_currency_id');
        $userWithdrawSettings = UserWithdrawSetting::where('user_id', $id)->get()->keyBy('withdraw_method_id');
        
        return view('admin.users.limits_settings', compact('pageTitle', 'user', 'gatewayCurrencies', 'withdrawMethods', 'forms', 'userDepositSettings', 'userWithdrawSettings'));
    }

    public function editDepositSetting($userId, $gatewayCurrencyId)
    {
        $user = User::findOrFail($userId);
        $gatewayCurrency = GatewayCurrency::with('method')->findOrFail($gatewayCurrencyId);
        $pageTitle = 'Configure Deposit Form - ' . $gatewayCurrency->name;
        
        $setting = UserDepositSetting::where('user_id', $userId)->where('gateway_currency_id', $gatewayCurrencyId)->first();
        $form = $setting && $setting->form_id ? Form::find($setting->form_id) : null;
        
        return view('admin.users.deposit_limit_edit', compact('pageTitle', 'user', 'gatewayCurrency', 'setting', 'form'));
    }

    public function updateDepositSetting(Request $request, $id)
    {
        $request->validate([
            'gateway_currency_id' => 'required|integer',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'fixed_charge' => 'required|numeric|min:0',
            'percent_charge' => 'required|numeric|min:0',
            'form_title' => 'nullable|string',
            'wallet_address' => 'nullable|string',
            'payment_info' => 'nullable|string'
        ]);

        $gatewayCurrency = GatewayCurrency::findOrFail($request->gateway_currency_id);
        
        $setting = UserDepositSetting::firstOrNew([
            'user_id' => $id,
            'gateway_currency_id' => $request->gateway_currency_id,
        ]);
        
        $formProcessor = new \App\Lib\FormProcessor();
        $request->validate($formProcessor->generatorValidation());
        $generate = $formProcessor->generate('user_deposit_override', true, 'id', $setting->form_id);
        
        $setting->min_amount = $request->min_amount;
        $setting->max_amount = $request->max_amount;
        $setting->fixed_charge = $request->fixed_charge;
        $setting->percent_charge = $request->percent_charge;
        $setting->form_title = $request->form_title;
        $setting->wallet_address = $request->wallet_address;
        $setting->payment_info = $request->payment_info;
        $setting->form_id = @$generate->id ?? 0;
        $setting->save();

        $notify[] = ['success', 'Deposit setting override saved successfully'];
        return back()->withNotify($notify);
    }
    
    public function removeDepositSetting($id, $setting_id)
    {
        UserDepositSetting::where('user_id', $id)->where('id', $setting_id)->delete();
        $notify[] = ['success', 'Deposit setting override removed successfully'];
        return back()->withNotify($notify);
    }

    public function editWithdrawSetting($userId, $withdrawMethodId)
    {
        $user = User::findOrFail($userId);
        $withdrawMethod = WithdrawMethod::findOrFail($withdrawMethodId);
        $pageTitle = 'Configure Withdraw Form - ' . $withdrawMethod->name;
        
        $setting = UserWithdrawSetting::where('user_id', $userId)->where('withdraw_method_id', $withdrawMethodId)->first();
        $form = $setting && $setting->form_id ? Form::find($setting->form_id) : null;
        
        return view('admin.users.withdraw_limit_edit', compact('pageTitle', 'user', 'withdrawMethod', 'setting', 'form'));
    }

    public function updateWithdrawSetting(Request $request, $id)
    {
        $request->validate([
            'withdraw_method_id' => 'required|integer',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'fixed_charge' => 'required|numeric|min:0',
            'percent_charge' => 'required|numeric|min:0',
            'form_title' => 'nullable|string',
            'wallet_address' => 'nullable|string',
            'payment_info' => 'nullable|string'
        ]);
        
        $setting = UserWithdrawSetting::firstOrNew([
            'user_id' => $id,
            'withdraw_method_id' => $request->withdraw_method_id,
        ]);
        
        $formProcessor = new \App\Lib\FormProcessor();
        $request->validate($formProcessor->generatorValidation());
        $generate = $formProcessor->generate('user_withdraw_override', true, 'id', $setting->form_id);
        
        $setting->min_amount = $request->min_amount;
        $setting->max_amount = $request->max_amount;
        $setting->fixed_charge = $request->fixed_charge;
        $setting->percent_charge = $request->percent_charge;
        $setting->form_title = $request->form_title;
        $setting->wallet_address = $request->wallet_address;
        $setting->payment_info = $request->payment_info;
        $setting->form_id = @$generate->id ?? 0;
        $setting->save();

        $notify[] = ['success', 'Withdraw setting override saved successfully'];
        return back()->withNotify($notify);
    }
    
    public function removeWithdrawSetting($id, $setting_id)
    {
        UserWithdrawSetting::where('user_id', $id)->where('id', $setting_id)->delete();
        $notify[] = ['success', 'Withdraw setting override removed successfully'];
        return back()->withNotify($notify);
    }
}
