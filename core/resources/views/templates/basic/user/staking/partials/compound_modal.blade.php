<div class="modal fade" id="compoundModal" tabindex="-1" aria-labelledby="compoundModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compoundModalLabel">@lang('Confirm Compound')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.staking.compound') }}" method="POST">
                @csrf
                <input type="hidden" name="stake_id">
                <div class="modal-body">
                    <p>@lang('Are you sure you want to compound your rewards? This will add your accumulated rewards to your staked amount.')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn-success">@lang('Compound Now')</button>
                </div>
            </form>
        </div>
    </div>
</div>