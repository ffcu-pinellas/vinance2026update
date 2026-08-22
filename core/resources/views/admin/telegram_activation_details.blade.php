@extends('admin.layouts.app')
@section('panel')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('admin.telegram-activations.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to list
            </a>
            <h1 class="h3 mb-0 text-gray-800 mt-3">Activation Details</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">User ID:</dt>
                        <dd class="col-sm-8">{{ $activation->user_id }}</dd>

                        <dt class="col-sm-4">Username:</dt>
                        <dd class="col-sm-8">{{ $activation->username }}</dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $activation->email }}</dd>

                        <dt class="col-sm-4">Account Created:</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($activation->user_created_at)->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activation Details</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Telegram Username:</dt>
                        <dd class="col-sm-8">{{ $activation->telegram_username }}</dd>

                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            @if($activation->status == 1)
                                <span class="badge badge-success">Approved</span>
                            @elseif($activation->status == 2)
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </dd>

                        @if($activation->status == 1)
                        <dt class="col-sm-4">Approved On:</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($activation->activated_at)->format('M d, Y H:i') }}</dd>

                        <dt class="col-sm-4">Approved By:</dt>
                        <dd class="col-sm-8">{{ $activation->approved_by_name ?? 'System' }}</dd>
                        @endif

                        @if($activation->status == 2 && $activation->rejection_reason)
                        <dt class="col-sm-4">Rejection Reason:</dt>
                        <dd class="col-sm-8">{{ $activation->rejection_reason }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection