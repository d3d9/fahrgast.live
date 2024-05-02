<div class="card mb-4">
    <div class="card-header">
        <ul class="nav nav-tabs" id="autocomplete-nav" role="tablist">
            <li class="nav-item" role="presentation">
                <a
                    data-mdb-tab-init
                    class="nav-link active"
                    data-mdb-toggle="tab"
                    id="san-station-tab"
                    href="#san-station"
                    role="tab"
                    aria-controls="san-station"
                    aria-selected="true"
                    >{{__('stationboard.where-are-you')}}</a
                >
            </li>
            <li class="nav-item" role="presentation">
                <a
                    data-mdb-tab-init
                    class="nav-link"
                    data-mdb-toggle="tab"
                    id="san-journey-tab"
                    href="#san-journey"
                    role="tab"
                    aria-controls="san-journey"
                    aria-selected="false"
                    >{{__('stationboard.plan-journey')}}</a
                >
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="autocomplete-content">
            <div
                class="tab-pane fade show active"
                id="san-station"
                role="tabpanel"
                aria-labelledby="san-station-tab"
            >
                @include('includes.station-autocomplete-content')
            </div>
            <div class="tab-pane fade" id="san-journey" role="tabpanel" aria-labelledby="san-journey-tab">
                @include('includes.journey-autocomplete-content')
            </div>
        </div>
    </div>
</div>
