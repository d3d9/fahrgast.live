<div class="modal fade" tabindex="-1" role="dialog" id="edit-destination-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('status.updateDestination')}}" id="status-update-destination">
                @csrf

                <input type="hidden" name="statusId"/>

                <div class="modal-header">
                    <h4 class="modal-title">{{__('modals.editStatusDestination-title')}}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="edit-status-destination-context" style="font-size: small;" class="mb-2 text-muted">Fahrt &mdash;</p>

                    <div class="destination-wrapper form-floating mb-2">
                        <select name="destinationStopoverId" class="form-select" required
                                id="form-status-destination"></select>
                        <label class="form-label" for="form-status-destination">
                            {{__('exit')}}
                        </label>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{__('menu.discard')}}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{__('modals.edit-confirm')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
