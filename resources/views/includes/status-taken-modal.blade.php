@php
use App\Enum\NotTakenReason;
@endphp
<div class="modal fade" tabindex="-1" role="dialog" id="status-taken-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('status.updateTaken')}}" id="status-taken">
                @csrf

                <input type="hidden" name="statusId"/>

                <div class="modal-header">
                    <h4 class="modal-title">{{__('modals.statusTaken-title')}}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="edit-status-taken-context" style="font-size: small;" class="mb-2 text-muted">Fahrt &mdash;</p>

                    <div class="mb-2" id="form-status-taken-edit-wrap">
                        <label class="form-label" style="color: inherit;" for="status_taken">
                            Fahren Sie in dieser Fahrt mit?&nbsp;@include('includes.required-star')
                        </label>
                        <div class="btn-group" id="status_taken" style="width: 100%;">
                            <input type="radio" class="btn-check" name="taken" id="status_taken_" value="" autocomplete="off" checked />
                            <label class="btn btn-secondary" style="flex: 33.3% 0;" for="status_taken_" data-mdb-ripple-init>Ausstehend</label>

                            <input type="radio" class="btn-check" name="taken" id="status_taken_1" value="1" autocomplete="off" />
                            <label class="btn btn-success" style="flex: 33.3% 0;" for="status_taken_1" data-mdb-ripple-init>Ja</label>

                            <input type="radio" class="btn-check" name="taken" id="status_taken_0" value="0" autocomplete="off" />
                            <label class="btn btn-danger" style="flex: 33.3% 0;" for="status_taken_0" data-mdb-ripple-init>Nein</label>
                        </div>
                    </div>
                    <div class="mb-2" id="form-status-not-taken-reason-edit-wrap">
                        <label class="form-label" style="color: inherit;" for="status_not_taken_reason">
                            Wieso sind Sie nicht mitgefahren?&nbsp;@include('includes.required-star')
                        </label>
                        @php
                        $nTO = function(NotTakenReason $r) { return '<option value="' . $r->value . '">' . htmlspecialchars($r->getReason()) . '</option>'; }
                        @endphp
                        <select name="not_taken_reason" id="status_not_taken_reason" class="form-select">
                            <option value="" disabled selected>Bitte auswählen</option>
                            <optgroup label="Problem mit einer vorherigen Fahrt">
                                {!!$nTO(NotTakenReason::MISSED)!!}
                                {!!$nTO(NotTakenReason::PREV_DEVIATION)!!}
                            </optgroup>
                            <optgroup label="Problem mit dieser Fahrt">
                                {!!$nTO(NotTakenReason::DELAYED)!!}
                                {!!$nTO(NotTakenReason::EARLY_DEP)!!}
                                {!!$nTO(NotTakenReason::CANCELLED)!!}
                                {!!$nTO(NotTakenReason::OVERCROWDED)!!}
                                {!!$nTO(NotTakenReason::DIFF_EXIT)!!}
                            </optgroup>
                            <optgroup label="Problem mit einer späteren Fahrt">
                                {!!$nTO(NotTakenReason::ADV_DEVIATION)!!}
                            </optgroup>
                            <optgroup label="Sonstiges">
                                {!!$nTO(NotTakenReason::BETTER_ALTERNATIVE)!!}
                                {!!$nTO(NotTakenReason::OTHER)!!}
                            </optgroup>
                        </select>
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
