@foreach($trades as $trade)
    <tr>
        <td class="text-center">
            <span class="badge bg-{{ $trade->order_side == 1 ? 'success' : 'danger' }}">
                {{ $trade->order_side == 1 ? 'Buy' : 'Sell' }}
            </span>
        </td>
        <td class="text-center">
            {{ $trade->pair->symbol }}
        </td>
        <td class="text-center">
            {{ showAmount($trade->amount) }}
        </td>
        <td class="text-center">
            {{ showAmount($trade->price) }}
        </td>
        <td class="text-center">
            {{ showAmount($trade->profit) }}
        </td>
        <td class="text-center">
            <span class="badge bg-{{ $trade->status == 0 ? 'warning' : ($trade->status == 1 ? 'success' : 'danger') }}">
                @if($trade->status == 0)
                    Pending
                @elseif($trade->status == 1)
                    Completed
                @else
                    Failed
                @endif
            </span>
        </td>
        <td class="text-center">
            {{ showDateTime($trade->created_at) }}
        </td>
        <td class="text-end pe-4">
            <button class="btn btn-sm btn-outline--primary trade-details" data-trade="{{ $trade }}">
                <i class="las la-desktop"></i> Details
            </button>
        </td>
    </tr>
@endforeach

@if($trades->isEmpty())
    <tr>
        <td colspan="8" class="text-center">No trades found</td>
    </tr>
@endif