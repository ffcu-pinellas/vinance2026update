@extends('admin.layouts.app')
@section('panel')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Telegram Bot Activation Requests</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Pending Activations</h6>
                    <span class="badge badge-primary">{{ $pending->total() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Telegram</th>
                                    <th>Request Date</th>
                                    <th>Account Age</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pending as $request)
                                <tr>
                                    <td>{{ $request->user_id }}</td>
                                    <td>{{ $request->username }}</td>
                                    <td>{{ $request->email }}</td>
                                    <td>{{ $request->telegram_username }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->user_created_at)->diffForHumans() }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <form method="POST" action="{{ route('admin.telegram-activations.approve', $request->id) }}" class="mr-2">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-danger reject-btn" 
                                                    data-toggle="modal" 
                                                    data-target="#rejectModal"
                                                    data-id="{{ $request->id }}">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No pending activation requests</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $pending->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Approved Activations</h6>
                    <span class="badge badge-success">{{ $active->total() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Telegram</th>
                                    <th>Approved On</th>
                                    <th>Approved By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($active as $connection)
                                <tr>
                                    <td>{{ $connection->user_id }}</td>
                                    <td>{{ $connection->username }}</td>
                                    <td>{{ $connection->telegram_username }}</td>
                                    <td>{{ \Carbon\Carbon::parse($connection->activated_at)->format('M d, Y H:i') }}</td>
                                    <td>{{ $connection->approved_by_name ?? 'System' }}</td>
                                    <td>
                                        <a href="{{ route('admin.telegram-activations.details', $connection->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No approved activations yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $active->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Activation Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Reason for rejection</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    $('.reject-btn').click(function() {
        var id = $(this).data('id');
        var form = $('#rejectForm');
        form.attr('action', '/admin/telegram-activations/reject/' + id);
    });
});
</script>
@endpush