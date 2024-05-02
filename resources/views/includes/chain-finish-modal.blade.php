@php
use App\Enum\TravelChainFinished;
@endphp
<div class="modal fade" tabindex="-1" role="dialog" id="chain-finish-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{route('travelchain.updateFinish')}}" id="chain-finish">
                @csrf

                <input type="hidden" name="chainId"/>

                <div class="modal-header">
                    <h4 class="modal-title">{{__('modals.chainFinish-title')}}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        {{__('modals.chainFinish-body')}}
                    </p>
                    <p id="chain-finish-ddd-context" style="font-size: small;" class="mb-3 text-muted">Zur Information: Die Entfernung zwischen geplantem Ziel und tatsächlichem Ziel beträgt laut den vorliegenden Daten <span id="ddd-value">&mdash;</span> Meter.</p>
                    <div class="mb-2" id="form-chain-finish-edit-wrap">
                        <label class="form-label" style="color: inherit;" for="chain_finished">
                            Wie ist die Reisekette geendet?
                        </label>
                        @php
                        $cFO = function(TravelChainFinished $r) { return '<option value="' . $r->value . '">' . htmlspecialchars($r->getReason()) . '</option>'; }
                        @endphp
                        <select name="finished" id="chain_finished" class="form-select">
                            <option value="" selected>Noch nicht beendet</option>
                            <optgroup label="Mit dem ÖPNV angekommen">
                                {!!$cFO(TravelChainFinished::ARRIVED)!!}
                                {!!$cFO(TravelChainFinished::ARRIVED_DIFF_DEST)!!}
                            </optgroup>
                            <optgroup label="Reise im ÖPNV abgebrochen">
                                {!!$cFO(TravelChainFinished::ABORTED_DIFF_MODE)!!}
                                {!!$cFO(TravelChainFinished::ABORTED)!!}
                            </optgroup>
                            <optgroup label="Sonstiges">
                                {!!$cFO(TravelChainFinished::OTHER)!!}
                            </optgroup>
                        </select>
                    </div>
                    <div id="finished_not_null_wrap">
                        @include('includes.likert-agreement-intro')
                        <div id="finished_arrived_wrap">
                            @include('includes.likert-select-radio', ['name' => 'felt_punctual', 'labelType' => "agreement", 'label' => "Ich empfand diese Reise als pünktlich"])
                        </div>
                        @include('includes.likert-select-radio', ['name' => 'felt_stressed', 'labelType' => "agreement", 'label' => "Ich war während der Reise gestresst"])
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
