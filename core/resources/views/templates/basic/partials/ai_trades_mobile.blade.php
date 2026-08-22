@foreach($trades as $trade)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted">Pair</small>
                    <h6>{{ $trade->pair->symbol }}</h6>
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted">Type</small>
                    <h6>
                        <span class="badge bg-{{ $trade->order_side == 1 ? 'success' : 'danger' }}">
                            {{ $trade->order_side == 1 ? 'Buy' : 'Sell' }}
                        </span>
                    </h6>
                </div>
            </div>
            
            <div class="row mt-2">
                <div class="col-6">
                    <small class="text-muted">Amount</small>
                    <h6>{{ showAmount($trade->amount) }}</h6>
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted">Price</small>
                    <h6>{{ showAmount($trade->price) }}</h6>
                </div>
            </div>
            
            <div class="row mt-2">
                <div class="col-6">
                    <small class="text-muted">Profit</small>
                    <h6 class="text-{{ $trade->profit >= 0 ? 'success' : 'danger' }}">
                        {{ showAmount($trade->profit) }}
                    </h6>
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted">Status</small>
                    <h6>
                        <span class="badge bg-{{ $trade->status == 0 ? 'warning' : ($trade->status == 1 ? 'success' : 'danger') }}">
                            @if($trade->status == 0)
                                Pending
                            @elseif($trade->status == 1)
                                Completed
                            @else
                                Failed
                            @endif
                        </span>
                    </h6>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-3">
                <small>{{ showDateTime($trade->created_at, 'd M, Y h:i A') }}</small>
                <button class="btn btn-sm btn-outline--primary trade-details" data-trade="{{ $trade }}">
                    <i class="las la-info-circle"></i> Details
                </button>
            </div>
        </div>
    </div>
@endforeach

@if($trades->isEmpty())
    <div class="alert alert-info text-center">No trades found</div>
@endif