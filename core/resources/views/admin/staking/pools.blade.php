@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('Staking Pools')</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Pool ID')</th>
                                <th>@lang('Token')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('APY Rate')</th>
                                <th>@lang('Lock Period')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pools as $pool)
                            <tr>
                                <td>{{ $pool->id }}</td>
                                <td>{{ $pool->configuration->token_name }} ({{ $pool->configuration->token_symbol }})</td>
                                <td>
                                    <span class="badge bg-{{ $pool->type == 'locked' ? 'primary' : 'success' }}">
                                        {{ ucfirst($pool->type) }}
                                    </span>
                                </td>
                                <td>{{ $pool->apy_rate }}%</td>
                                <td>{{ $pool->type == 'locked' ? $pool->lock_period_days.' days' : 'Flexible' }}</td>
                                <td>
                                    @if($pool->is_active)
                                        <span class="badge bg-success">@lang('Active')</span>
                                    @else
                                        <span class="badge bg-danger">@lang('Inactive')</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-pool" 
                                        data-id="{{ $pool->id }}"
                                        data-apy="{{ $pool->apy_rate }}"
                                        data-active="{{ $pool->is_active }}">
                                        <i class="las la-pen"></i> @lang('Edit')
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="100%" class="text-center">@lang('No pools found')</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($pools->hasPages())
                    <div class="mt-3">
                        {{ $pools->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Pool Modal -->
<div class="modal fade" id="editPoolModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Edit Pool')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('APY Rate') (%)</label>
                        <input type="number" step="0.01" class="form-control" name="apy_rate" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Status')</label>
                        <select class="form-control" name="is_active" required>
                            <option value="1">@lang('Active')</option>
                            <option value="0">@lang('Inactive')</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-primary">@lang('Update')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function($) {
        "use strict";
        
        $('.edit-pool').on('click', function() {
            var modal = $('#editPoolModal');
            var id = $(this).data('id');
            var apy = $(this).data('apy');
            var active = $(this).data('active');
            
            modal.find('form').attr('action', '{{ route("admin.staking.update.pool", "") }}/'+id);
            modal.find('[name=apy_rate]').val(apy);
            modal.find('[name=is_active]').val(active);
            
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush