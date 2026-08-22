@component('mail::message')
# AI Trader Settings Updated - Admin Notification

A user has updated their AI Trader settings:

**User**: {{ $user->username }} ({{ $user->email }})  
**Risk Level**: {{ ucfirst($settings['risk_level']) }}  
**Strategy**: {{ ucfirst($settings['trading_strategy']) }}  
**AI Auto Trader Balance Threshold**: {{ $settings['max_trades'] }}  
**Trading Pairs**: {{ implode(', ', json_decode($settings['trading_pairs'], true)) }}  

**Notification Settings**:  
Telegram: {{ $settings['telegram_notifications'] ? 'ON' : 'OFF' }}  

**Auto Trading**: {{ $settings['auto_trade'] ? 'ENABLED' : 'DISABLED' }}

@component('mail::button', ['url' => url('/admin/users')])
View All Users
@endcomponent

<small style="color: #666;">
This is an automated notification sent to the system administrator.
</small>
@endcomponent