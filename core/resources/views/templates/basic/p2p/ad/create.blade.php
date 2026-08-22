@extends('Template::layouts.app')

@section('content')
<!doctype html>
<html lang="en" itemscope itemtype="http://schema.org/WebPage">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Vinance - Create Advertisement</title>
    <meta name="title" content="Vinance - Create Advertisement">
    <meta name="description" content="VINANCE- Digital Trading Platform. That will take your excitement to the next level! Get ready to experience the ultimate thrill of winning as we bring you a cutting-edge trading experience with Vinance.">
    <meta name="keywords" content="trading,crypto currency,fiat currency,crypto sell,crypto buy,vinance">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo_icon/favicon.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/logo_icon/logo.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Vinance - Create Advertisement">
    <meta itemprop="name" content="Vinance - Create Advertisement">
    <meta itemprop="description" content="VINANCE- Digital Trading Platform. That will take your excitement to the next level! Get ready to experience the ultimate thrill of winning as we bring you a cutting-edge trading experience with Vinance.">
    <meta itemprop="image" content="{{ asset('assets/images/seo/6665ae67bc43e1717939815.jpg') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Vinance - Digital Trading Platform">
    <meta property="og:description" content="VINANCE- Digital Trading Platform. That will take your excitement to the next level! Get ready to experience the ultimate thrill of winning as we bring you a cutting-edge trading experience with Vinance.">
    <meta property="og:image" content="{{ asset('assets/images/seo/6665ae67bc43e1717939815.jpg') }}">
    <meta property="og:image:type" content="image/jpg">
    <meta property="og:image:width" content="1180">
    <meta property="og:image:height" content="600">
    <meta property="og:url" content="{{ url('/user/p2p/advertisement/create') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/templates/basic/dashboard/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/templates/basic/dashboard/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/templates/basic/css/custom.css') }}">
    <style>
        .select2-container--default .select2-selection--multiple {
            padding: 10px;
            background-color: transparent;
            border-color: hsl(var(--white)/0.14);
            color: hsl(var(--body-color));
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: hsl(var(--base));
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: hsl(var(--base)/0.5);
            color: #fff;
            padding: 5px 2px;
            border: unset;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/templates/basic/dashboard/css/p2p.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/templates/basic/css/color.php?color=808000') }}">
</head>
<body>
    <div class="preloader">
        <div class="loader-p"></div>
    </div>
    <div class="body-overlay"></div>
    <div class="sidebar-overlay"></div>
    <a class="scroll-top"><i class="fas fa-angle-double-up"></i></a>

    <div class="dashboard-fluid position-relative">
        <div class="dashboard__inner">
            <!-- Sidebar and Header Code Here (Copy from your original code) -->

            <div class="dashboard-body">
                <div class="d-flex justify-content-between mb-3 align-items-center dashboardBodyNav">
                    <div class="dashboard-body__bar d-xl-none d-inline-block">
                        <button class="dashboard-sidebar-filter__button">
                            <i class="las la-bars"></i>
                        </button>
                    </div>
                    <div class="p2p-sidebar__menu">
                        <span class="p2p-sidebar__menu-icon">
                            <i class="fas fa-bars"></i>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxl-3 col-xl-4">
                        <!-- Sidebar Code Here (Copy from your original code) -->
                    </div>
                    <div class="col-xxl-9 col-xl-8">
                        <div class="p2p-form">
                            <div class="p2p-form__wrapper">
                                <div class="p2p-form__tab">
                                    <span class="p2p-form__tab-button ad-type side-buy {{ $step == 1 ? 'active' : '' }}" data-type="1">Buy</span>
                                    <span class="p2p-form__tab-button ad-type side-sell {{ $step == 2 ? 'active' : '' }}" data-type="2">Sell</span>
                                </div>
                            </div>
                            <div class="p2p-form__content">
                                <form class="p2p-form-box" method="POST" action="{{ route('user.p2p.advertisement.save', $ad->id ?? 0) }}">
                                    @csrf
                                    <input type="hidden" name="step" value="{{ $step }}">
                                    <div class="row">
                                        <!-- Step 1: AD Type, Asset, Fiat -->
                                        @if($step == 1)
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">AD Type</label>
                                                <select class="form-control form--control form-select select2" name="type" required>
                                                    <option value="1" {{ $ad->type == 1 ? 'selected' : '' }}>Buy</option>
                                                    <option value="2" {{ $ad->type == 2 ? 'selected' : '' }}>Sell</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">Asset</label>
                                                <select class="form-control form--control form-select select2" name="asset" required>
                                                    @foreach($cryptoAssets as $asset)
                                                        <option value="{{ $asset->id }}" {{ $ad->asset_id == $asset->id ? 'selected' : '' }}>{{ $asset->symbol }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">Fiat</label>
                                                <select class="form-control form--control form-select select2" name="fiat" required>
                                                    @foreach($fiatCurrencies as $fiat)
                                                        <option value="{{ $fiat->id }}" {{ $ad->fiat_id == $fiat->id ? 'selected' : '' }}>{{ $fiat->symbol }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <!-- Step 2: Pricing, Payment Methods -->
                                        @if($step == 2)
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">Payment Method</label>
                                                <select class="form-control form--control form-select select2" name="payment_method[]" required multiple>
                                                    @foreach($paymentMethods as $method)
                                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">Payment Window</label>
                                                <select class="form-control form--control form-select select2" name="payment_window" required>
                                                    @foreach($paymentWindows as $window)
                                                        <option value="{{ $window->id }}">{{ $window->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">Pricing Type</label>
                                                <select class="form-control form--control form-select select2" name="price_type" required>
                                                    <option value="1" {{ $ad->price_type == 1 ? 'selected' : '' }}>Fixed</option>
                                                    <option value="2" {{ $ad->price_type == 2 ? 'selected' : '' }}>Margin</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-lg-12 margin-input-wrapper {{ $ad->price_type == 1 ? 'd-none' : '' }}">
                                                <label class="form-label">Margin</label>
                                                <div class="input-group">
                                                    <span class="input-group-text marginDecrement cursor-pointer"><i class="las la-minus"></i></span>
                                                    <input type="number" step="any" class="form-control form--control" name="margin" value="{{ $ad->price_margin ?? 100 }}" required>
                                                    <span class="input-group-text marginIncrement cursor-pointer"><i class="las la-plus"></i></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-lg-12 price-input-wrapper">
                                                <label class="form-label">Price</label>
                                                <div class="input-group coin-price">
                                                    <input type="number" step="any" class="form-control form--control" name="price" value="{{ $ad->price ?? 0 }}" required>
                                                </div>
                                                <span class="mt-2 fs-13">{{ $ad->asset->symbol ?? 'N/A' }} = {{ $ad->price ?? 0 }} {{ $ad->fiat->symbol ?? 'N/A' }}</span>
                                            </div>
                                            <div class="form-group col-lg-6">
                                                <label class="form-label">Minimum Amount</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" class="form-control form--control" name="minimum_amount" value="{{ $ad->minimum_amount ?? 0 }}" required>
                                                    <span class="input-group-text">{{ $ad->fiat->symbol ?? 'USD' }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group col-lg-6">
                                                <label class="form-label">Maximum Amount</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" class="form-control form--control" name="maximum_amount" value="{{ $ad->maximum_amount ?? 0 }}" required>
                                                    <span class="input-group-text">{{ $ad->fiat->symbol ?? 'USD' }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Step 3: Payment Details, Terms of Trade -->
                                        @if($step == 3)
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">Payment Details</label>
                                                <textarea class="form-control form--control" name="payment_details" required>{{ $ad->payment_details ?? '' }}</textarea>
                                            </div>
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">Terms of Trade</label>
                                                <textarea class="form-control form--control" name="terms_of_trade" required>{{ $ad->terms_of_trade ?? '' }}</textarea>
                                            </div>
                                            <div class="form-group col-lg-12">
                                                <label class="form-label">Auto Reply Text</label>
                                                <textarea class="form-control form--control" name="auto_replay_text">{{ $ad->auto_replay_text ?? '' }}</textarea>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Navigation Buttons -->
                                    <div class="d-flex justify-content-between mt-4">
                                        @if($step > 1)
                                            <a href="{{ route('user.p2p.advertisement.create', ['id' => $ad->id ?? 0, 'step' => $step - 1]) }}" class="btn btn--base outline">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        @endif
                                        <button type="submit" class="btn btn--base ms-auto">
                                            {{ $step == 3 ? 'Submit' : 'Next' }} <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/templates/basic/dashboard/js/main.js') }}"></script>
    <script>
        $(document).ready(function() {
            // JavaScript for dynamic behavior (e.g., price calculation)
            $(`select[name=price_type]`).on(`change`, function(e) {
                const pricingType = $(this).val();
                if (pricingType == 1) {
                    $(`.margin-input-wrapper`).addClass(`d-none`);
                    $(`.price-input-wrapper`).removeClass(`col-lg-12`);
                } else {
                    $(`.margin-input-wrapper`).removeClass(`d-none`);
                    $(`.price-input-wrapper`).addClass(`col-lg-12`);
                }
                calculate();
            });

            function calculate() {
                const coinPrice = Number("{{ $ad->asset->marketData->price ?? 0 }}");
                const currencyPrice = Number("{{ $ad->fiat->rate ?? 1 }}");
                const price = Number("{{ $ad->price ?? 0 }}");

                if (!coinPrice || !currencyPrice || !price) return;

                const priceType = Number($("select[name=price_type]").val());

                if (priceType == 1) {
                    $(`input[name=price]`).val(price);
                } else {
                    const margin = Number($("input[name=margin]").val());
                    const modifyPrice = (price / 100) * margin;
                    $(`input[name=price]`).val(modifyPrice);
                    $(`input[name=price]`).attr('readonly', true);
                }
            }

            $('.marginDecrement').on('click', function(e) {
                let value = Number($(`input[name=margin]`).val());
                if (value <= 1) return;
                value = value - 1;
                $(`input[name=margin]`).val(value);
                calculate();
            });

            $('.marginIncrement').on('click', function(e) {
                let value = Number($(`input[name=margin]`).val());
                value = value + 1;
                $(`input[name=margin]`).val(value);
                calculate();
            });

            $('input[name=margin]').on('input', function(e) {
                let value = Number($(this).val());
                if (value < 0) $(this).val(0);
                calculate();
            });
        });
    </script>
</body>
</html>
@endsection