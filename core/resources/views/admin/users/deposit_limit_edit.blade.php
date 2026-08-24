@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('admin.users.limits.settings.deposit.update', $user->id) }}" method="POST">
                @csrf
                <input type="hidden" name="gateway_currency_id" value="{{ $gatewayCurrency->id }}">
                
                <div class="card mb-4 border--primary">
                    <div class="card-header bg--primary">
                        <h5 class="text-white mb-0"><i class="las la-cog"></i> @lang('Deposit Configuration for') {{ $user->username }} ({{ $gatewayCurrency->name }})</h5>
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
                        <h5 class="mb-3"><i class="las la-wallet"></i> @lang('User-Specific Payment & QR Details')</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Custom Form Title (Optional)')</label>
                                    <input type="text" name="form_title" class="form-control" value="{{ old('form_title', $setting ? $setting->form_title : '') }}" placeholder="e.g. Deposit {{ $gatewayCurrency->currency }} - Fast Verification">
                                </div>
                                <div class="form-group">
                                    <label>@lang('Custom Wallet Address (Optional)')</label>
                                    <div class="input-group">
                                        <input type="text" name="wallet_address" id="adminWalletAddress" class="form-control" value="{{ old('wallet_address', $setting ? $setting->wallet_address : '') }}" placeholder="e.g. 0x123... / TRC20 / ERC20 Address">
                                        <button class="btn btn--outline-primary" type="button" onclick="copyAdminWallet()"><i class="las la-copy"></i> @lang('Copy')</button>
                                    </div>
                                    <small class="text-muted">@lang('Provide a unique deposit wallet for this user. The system will auto-generate the QR code for it on the user frontend.')</small>
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
                    <div class="card-header bg--primary d-flex justify-content-between align-items-center">
                        <h5 class="text-white mb-0"><i class="las la-sliders-h"></i> @lang('Custom Form Builder (On The Go)')</h5>
                        <button type="button" class="btn btn-sm btn-outline-light float-end form-generate-btn">
                            <i class="la la-fw la-plus"></i>@lang('Add New Field')
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">@lang('Create custom input fields specifically for this user when they make a deposit (e.g. Transaction Hash, Proof Screenshot, Memo ID).')</p>
                        <x-generated-form :form="$form" />
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn--primary w-100 h-45"><i class="las la-save"></i> @lang('Save Configuration')</button>
                </div>
            </form>
        </div>
    </div>
    
    <x-form-generator-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.limits.settings', $user->id) }}" class="btn btn-sm btn-outline--primary"><i class="las la-undo"></i> @lang('Back to Limits')</a>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/form_actions.js') }}"></script>
@endpush

@push('script')
<script>
    function copyAdminWallet() {
        var copyText = document.getElementById("adminWalletAddress");
        if (!copyText.value) {
            notify('error', 'No wallet address entered yet!');
            return;
        }
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        notify('success', 'Copied: ' + copyText.value);
    }
</script>
@endpush
