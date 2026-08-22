@component('mail::message')
# {{ $subject }}

{{ $content }}

@component('mail::button', ['url' => url('/staking')])
View Your Staking
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent