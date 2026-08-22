<div class="modal fade" id="unstakeModal" tabindex="-1" aria-labelledby="unstakeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unstakeModalLabel">@lang('Confirm Unstake')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.staking.unstake') }}" method="POST">
                @csrf
                <input type="hidden" name="stake_id">
                <div class="modal-body">
                    <p>@lang('Are you sure you want to unstake? This will return your staked amount plus any accumulated rewards to your funding wallet.')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn-danger">@lang('Unstake Now')</button>
                </div>
            </form>
        </div>
    </div>
</div>