@component('mail::message')
# {{ $subject }}

{!! nl2br(e($content)) !!}

**User Email:** {{ $user_email }}  
**Amount:** {{ $amount }} USDT  
**Pool:** {{ $pool_name }}  
**APY:** {{ $apy_rate }}%

@component('mail::button', ['url' => url('/admin/staking')])
View in Admin Panel
@endcomponent

Regards,<br>
{{ config('app.name') }} Administration
@endcomponent