<div class="modal fade" tabindex="-1" role="dialog" id="edit-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('status.update')}}" id="status-update">
                @csrf

                <input type="hidden" name="statusId"/>

                <div class="modal-header">
                    <h4 class="modal-title">{{__('modals.editStatus-title')}}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="edit-status-context" style="font-size: small;" class="mb-2 text-muted">Fahrt &mdash;</p>

                    @include('includes.chain-dropdown', ['id_suffix' => 'edit'])

                    <!-- destination edit moved -->

                    <div class="row">
                        <div class="accordion accordion-flush" id="accordionEditTime" style="color: inherit;">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOneTime">
                                    <button class="accordion-button collapsed"
                                            style="color: unset;"
                                            type="button"
                                            data-mdb-toggle="collapse"
                                            data-mdb-target="#edit-time-body"
                                            aria-expanded="false"
                                            aria-controls="edit-time-body"
                                    >
                                        Tatsächliche Ankunft/Abfahrt überschreiben
                                    </button>
                                </h2>
                                <div id="edit-time-body"
                                     class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingOneTime"
                                     data-mdb-parent="#accordionEditTime"
                                >
                                    <div class="accordion-body row">
                                        <div class="col-12 mb-2">
                                            <small>Wenn die automatischen Echtzeitdaten nicht vorliegen oder ungenau sind, können Sie hier selber die richtigen Zeitangaben machen.</small>
                                        </div>
                                        <div class="col-sm-6">
                                            <hr/>
                                            <div class="mb-2 text-muted">
                                                <small>
                                                    <span id="edit-status-manual-context-origin">&mdash;</span><br/>
                                                    Plan: <span id="edit-status-manual-context-planned-departure">&mdash;</span><br/>
                                                    Echtzeit: <span id="edit-status-manual-context-real-departure">&mdash;</span>
                                                </small>
                                            </div>
                                            <div class="form-floating mb-2">
                                                <input class="form-control" name="manualDeparture" id="manual_departure"
                                                       type="datetime-local"
                                                       placeholder="{{__('export.title.departure_manual')}}"
                                                />
                                                <label for="manual_departure">
                                                    {{__('export.title.departure_manual')}}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <hr/>
                                            <div class="mb-2 text-muted">
                                                <small>
                                                    <span id="edit-status-manual-context-destination">&mdash;</span><br/>
                                                    Plan: <span id="edit-status-manual-context-planned-arrival">&mdash;</span><br/>
                                                    Echtzeit: <span id="edit-status-manual-context-real-arrival">&mdash;</span>
                                                </small>
                                            </div>
                                            <div class="form-floating mb-2">
                                                <input class="form-control" name="manualArrival" id="manual_arrival"
                                                       type="datetime-local"
                                                       placeholder="{{__('export.title.arrival_manual')}}"
                                                />
                                                <label for="manual_arrival">
                                                    {{__('export.title.arrival_manual')}}
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-outline">
                        <textarea class="form-control" name="body" id="status-body" maxlength="280"
                                  placeholder="{{__('modals.editStatus-label')}}"
                                  style="min-height: 100px;"></textarea>
                        <label for="status-body" class="form-label">
                            {{__('modals.editStatus-label')}}
                        </label>
                    </div>
                    <small class="text-muted float-end"><span id="body-length">-</span>/280</small>
                    <script>
                        document.querySelector('#status-body').addEventListener('input', function (e) {
                            document.querySelector('#body-length').innerText = e.target.value.length;
                        });



                    </script>

                    <div class="mt-2">
                        <!--
                        {{-- @include('includes.business-dropdown') --}}
                        {{-- @include('includes.visibility-dropdown') --}}
                        -->
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
