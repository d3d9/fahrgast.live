<div class="modal fade" tabindex="-1" role="dialog" id="chain-edit-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('travelchain.update')}}" id="chain-update">
                @csrf

                <input type="hidden" name="chainId"/>

                <div class="modal-header">
                    <h4 class="modal-title">{{__('modals.editChain-title')}}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-outline">
                        <input type="text" required class="form-control" name="title" id="chain-title" maxlength="255"
                                  placeholder="{{__('modals.editChain-title-label')}}" />
                        <label for="chain-title" class="form-label">
                            {{__('modals.editChain-title-label')}}
                        </label>
                    </div>
                    <small class="text-muted float-end"><span id="chain-title-length">-</span>/255</small>
                    <div class="form-outline mt-4">
                        <textarea class="form-control" name="body" id="chain-body" maxlength="280"
                                  placeholder="{{__('modals.editChain-label')}}"
                                  style="min-height: 100px;"></textarea>
                        <label for="chain-body" class="form-label">
                            {{__('modals.editChain-label')}}
                        </label>
                    </div>
                    <small class="text-muted float-end"><span id="chain-body-length">-</span>/280</small>
                    <script>
                        document.querySelector('#chain-body').addEventListener('input', function (e) {
                            document.querySelector('#chain-body-length').innerText = e.target.value.length;
                        });

                        document.querySelector('#chain-title').addEventListener('input', function (e) {
                            document.querySelector('#chain-title-length').innerText = e.target.value.length;
                        });

                    </script>

                    <div class="mt-4">
                        @include('includes.business-select')
                        @include('includes.likert-agreement-intro')
                        @include('includes.likert-select-radio', ['name' => 'reliability_importance', 'labelType' => "agreement", 'label' => "Die Zuverlässigkeit ist mir bei dieser Reisekette wichtig"])
                        @include('includes.likert-select-radio', ['name' => 'planned_for_reliability', 'labelType' => "agreement", 'label' => "Ich habe die Zuverlässigkeit bei der Planung der Reisekette berücksichtigt"])
                        {{-- @include('includes.business-dropdown') --}
                        {{-- @include('includes.visibility-dropdown') --}}
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
