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

    public function updateDepositSetting(Request $request, $id)
    {
        $request->validate([
            'gateway_currency_id' => 'required|integer',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'fixed_charge' => 'required|numeric|min:0',
            'percent_charge' => 'required|numeric|min:0',
            'form_id' => 'nullable|integer'
        ]);

        $gatewayCurrency = GatewayCurrency::findOrFail($request->gateway_currency_id);
        
        $setting = UserDepositSetting::firstOrNew([
            'user_id' => $id,
            'gateway_currency_id' => $request->gateway_currency_id,
        ]);
        
        $setting->gateway_code = $gatewayCurrency->method_code;
        $setting->currency = $gatewayCurrency->currency;
        $setting->min_amount = $request->min_amount;
        $setting->max_amount = $request->max_amount;
        $setting->fixed_charge = $request->fixed_charge;
        $setting->percent_charge = $request->percent_charge;
        $setting->form_id = $request->form_id;
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

    public function updateWithdrawSetting(Request $request, $id)
    {
        $request->validate([
            'withdraw_method_id' => 'required|integer',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0',
            'fixed_charge' => 'required|numeric|min:0',
            'percent_charge' => 'required|numeric|min:0',
            'form_id' => 'nullable|integer'
        ]);
        
        $setting = UserWithdrawSetting::firstOrNew([
            'user_id' => $id,
            'withdraw_method_id' => $request->withdraw_method_id,
        ]);
        
        $setting->min_amount = $request->min_amount;
        $setting->max_amount = $request->max_amount;
        $setting->fixed_charge = $request->fixed_charge;
        $setting->percent_charge = $request->percent_charge;
        $setting->form_id = $request->form_id;
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
