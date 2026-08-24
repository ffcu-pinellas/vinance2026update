@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('admin.users.limits.settings.deposit.update', $user->id) }}" method="POST">
                @csrf
                <input type="hidden" name="gateway_currency_id" value="{{ $gatewayCurrency->id }}">
                
                <div class="card mb-4">
                    <div class="card-header bg--primary">
                        <h5 class="text-white mb-0">@lang('Configuration for') {{ $user->username }} - {{ $gatewayCurrency->name }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Minimum Amount')</label>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control" name="min_amount" value="{{ old('min_amount', $setting ? $setting->min_amount : $gatewayCurrency->min_amount) }}" required>
                                        <div class="input-group-text">{{ $gatewayCurrency->currency }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Maximum Amount')</label>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control" name="max_amount" value="{{ old('max_amount', $setting ? $setting->max_amount : $gatewayCurrency->max_amount) }}" required>
                                        <div class="input-group-text">{{ $gatewayCurrency->currency }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Fixed Charge')</label>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control" name="fixed_charge" value="{{ old('fixed_charge', $setting ? $setting->fixed_charge : $gatewayCurrency->fixed_charge) }}" required>
                                        <div class="input-group-text">{{ $gatewayCurrency->currency }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Percent Charge')</label>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control" name="percent_charge" value="{{ old('percent_charge', $setting ? $setting->percent_charge : $gatewayCurrency->percent_charge) }}" required>
                                        <div class="input-group-text">%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">@lang('User-Specific Payment Details')</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Custom Form Title (Optional)')</label>
                                    <input type="text" name="form_title" class="form-control" value="{{ old('form_title', $setting ? $setting->form_title : '') }}" placeholder="e.g. Deposit Instructions for VIP">
                                </div>
                                <div class="form-group">
                                    <label>@lang('Custom Wallet Address (Optional)')</label>
                                    <input type="text" name="wallet_address" class="form-control" value="{{ old('wallet_address', $setting ? $setting->wallet_address : '') }}" placeholder="e.g. 0x123...">
                                    <small class="text-muted">@lang('Provide a unique deposit wallet for this user. Will auto-generate QR.')</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Custom Payment Instructions (Optional)')</label>
                                    <textarea name="payment_info" class="form-control nicEdit" rows="5" placeholder="Special instructions for this user...">{{ old('payment_info', $setting ? $setting->payment_info : '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 border--primary">
                    <div class="card-header bg--primary d-flex justify-content-between">
                        <h5 class="text-white mb-0">@lang('Custom Form Builder')</h5>
                        <button type="button" class="btn btn-sm btn-outline-light float-end form-generate-btn">
                            <i class="la la-fw la-plus"></i>@lang('Add New Field')
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">@lang('Create custom input fields specifically for this user when they make a deposit.')</p>
                        <x-generated-form :form="$form" />
                    </div>
                </div>

                <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save Configuration')</button>
            </form>
        </div>
    </div>
    
    <x-form-generator-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.limits.settings', $user->id) }}" class="btn btn-sm btn-outline--primary"><i class="las la-undo"></i> @lang('Back')</a>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/form_actions.js') }}"></script>
@endpush
