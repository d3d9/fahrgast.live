<form action="{{ route('trains.journey-planner') }}" method="get" id="journey-form">
    <input type="hidden" name="earlierRef" id="earlierRef" value="" disabled />
    <input type="hidden" name="laterRef" id="laterRef" value="" disabled />

    <div id="origin-autocomplete-container" style="z-index: 4;">
        <div class="input-group mb-2 mr-sm-2">
            <input type="text" id="origin-autocomplete" name="location_origin" class="form-control"
                   placeholder="{{__('stationboard.location-origin-placeholder')}}"
                   value="{{request()->location_origin}}"
            />
            <input type="hidden" id="origin-autocomplete-value" class="location-value-source" name="location_origin_value" value="{{ request()->location_origin_value }}" />
            <div class="input-group-text location-value-state"><i class="fa fa-exclamation location-value-state--empty"></i><i class="fa fa-check location-value-state--set"></i></div>
        </div>
    </div>

    <div id="destination-autocomplete-container" style="z-index: 3;">
        <div class="input-group mb-2 mr-sm-2">
            <input type="text" id="destination-autocomplete" name="location_destination" class="form-control"
                   placeholder="{{__('stationboard.location-destination-placeholder')}}"
                   value="{{request()->location_destination}}"
            />
            <input type="hidden" id="destination-autocomplete-value" class="location-value-source" name="location_destination_value" value="{{ request()->location_destination_value }}" />
            <div class="input-group-text location-value-state"><i class="fa fa-exclamation location-value-state--empty"></i><i class="fa fa-check location-value-state--set"></i></div>
        </div>
    </div>

    <div class="input-group mb-3 mx-auto">
        <div class="input-group-text">
            <input type="radio" class="form-check-input" name="arr" id="deparr_dep" value="0" autocomplete="off"{{ (request()->arr ?? 0) == 0 ? " checked" : "" }} />
            <label class="ms-2" for="deparr_dep">Ab</label>
        </div>
        <div class="input-group-text">
            <input type="radio" class="form-check-input" name="arr" id="deparr_arr" value="1" autocomplete="off"{{ (request()->arr ?? 0) == 1 ? " checked" : "" }} />
            <label class="ms-2" for="deparr_arr">An</label>
        </div>
        <input type="datetime-local" class="form-control" id="timepicker" name="when"
               aria-describedby="button-addontime"
               value="{{ request()->when }}"/> {{-- userTime(request()->when, 'Y-m-d\TH:i', false) --}}
    </div>

    <button class="btn btn-outline-primary float-end" type="submit">
        {{__('stationboard.submit-search')}}
    </button>
    <button class="btn btn-outline-secondary" type="button" data-mdb-toggle="collapse"
            data-mdb-target="#collapseJourneyOptions" aria-expanded="false">
        Optionen
    </button>
    <div class="collapse" id="collapseJourneyOptions">
        <div class="mt-3">
            <div class="input-group mb-3 mx-auto">
                <label class="input-group-text" for="transferTime">Umstieg mindestens</label>
                <input id="transferTime" name="transferTime" min="0" type="number" class="form-control" placeholder="0" value="{{request()->transferTime}}" />
                <label class="input-group-text">min</label>
            </div>
        </div>
        <div class="mt-3">
            <div class="input-group mb-3 mx-auto">
                <label class="input-group-text" for="walkingSpeed">Gehgeschwindigkeit</label>
                @php
                    $speeds = ["slow" => "Langsam", "normal" => "Normal", "fast" => "Schnell"];
                @endphp
                <select name="walkingSpeed" id="walkingSpeed" class="form-control">
                    @foreach($speeds as $speed => $speedLabel)
                        @php
                            $selected = isset(request()->walkingSpeed) ? (request()->walkingSpeed === $speed) : $speed === "normal";
                        @endphp
                        <option value="{{ $speed }}"{{ $selected ? " selected" : "" }}>{{ $speedLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 d-flex justify-content-center">
            <div class="flex-wrap" role="group">
                @php
                    $travelTypes = ["bus", "tram", "subway", "suburban", "regional", "express", "taxi", "ferry"];
                @endphp
                @foreach($travelTypes as $tType)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox"{{ (empty(request()->travelType) || in_array($tType, request()->travelType)) ? " checked" : "" }} id="tt_{{ $tType }}" name="travelType[]" value="{{ $tType }}" />
                        <label class="form-check-label" for="tt_{{ $tType }}">{{ __('transport_types.' . $tType) }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</form>
