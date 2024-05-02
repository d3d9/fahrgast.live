@php
    use App\Enum\Business;
    use App\Http\Controllers\Backend\Transport\StationController;
    use App\Http\Controllers\Backend\User\ProfilePictureController;
@endphp
<div class="card status mb-3" id="status-{{ $status->id }}"
     data-trwl-id="{{$status->id}}"
     data-date="{{userTime($status->checkin->departure, __('dateformat.with-weekday'))}}"
     data-trwl-linename="{{$status->checkin->trip->linename}}"
     data-trwl-departure-planned="{{userTime($status->checkin->originStopover->departure_planned)}}"
     data-trwl-arrival-planned="{{userTime($status->checkin->destinationStopover->arrival_planned)}}"
     data-trwl-departure-real="{{userTime($status->checkin->originStopover->departure_real)}}"
     data-trwl-arrival-real="{{userTime($status->checkin->destinationStopover->arrival_real)}}"
     data-trwl-origin="{{$status->checkin->originStation->name}}"
     data-trwl-destination="{{$status->checkin->destinationStation->name}}"
     @if(auth()->check() && auth()->id() === $status->user_id)
         data-trwl-status-body="{{ $status->body }}"
         data-trwl-manual-departure="{{ userTime($status->checkin?->manual_departure, 'Y-m-d\TH:i:s', false)}}"
         data-trwl-manual-arrival="{{ userTime($status->checkin?->manual_arrival, 'Y-m-d\TH:i:s', false)}}"
         data-trwl-business-id="{{ $status->business->value }}"
         data-trwl-visibility="{{ $status->visibility->value }}"
         data-trwl-destination-stopover="{{$status->checkin->destinationStopover->id}}"
         data-trwl-alternative-destinations=
             "{{json_encode(StationController::getAlternativeDestinationsForCheckin($status->checkin))}}"
         data-trwl-chain-id="{{ $status->travelChain?->id }}"
         data-trwl-chain-label="{{ $status->travelChain?->title }}"
         data-trwl-planned="{{ isset($status->planned) ? intval($status->planned) : null }}"
         data-trwl-taken="{{ isset($status->taken) ? intval($status->taken) : null }}"
         data-trwl-not-taken-reason="{{ $status->not_taken_reason?->value }}"
    @endif
>
    @if (Route::current()->uri == "status/{id}")
        <div class="card-img-top">
            <div id="activeJourneys" class="map statusMap embed-responsive embed-responsive-16by9">
                <active-journey-map
                    map-provider="{{ Auth::user()->mapprovider ?? "default" }}"
                    :status-id="{{ $status->id }}"
                />
            </div>
        </div>
    @endif

    <div class="card-body row">
        <div class="col ps-0">
            <ul class="timeline">
                <li>
                    <i class="trwl-bulletpoint" aria-hidden="true"></i>
                    <span class="text-trwl float-end">
                        @php
                            $display_departure = $status->checkin->displayDeparture;
                        @endphp
                        @isset($display_departure->original)
                            <small style="text-decoration: line-through;" class="text-muted">
                                {{ userTime($display_departure->original) }}
                            </small>
                            &nbsp;
                        @endisset
                        <span data-mdb-toggle="tooltip" title="{{$display_departure->type->getTooltip()}}" @if($display_departure->cancelled) class="text-muted" @endif>
                            {{ userTime($display_departure->time) }}
                        </span>
                        @if($display_departure->cancelled)
                            <span class="text-danger">Ausfall</span>
                        @endif
                    </span>

                    <a href="{{route('trains.stationboard', ['provider' => 'train', 'station' => $status->checkin->originStation->ibnr])}}"
                       class="text-trwl clearfix">
                        {{$status->checkin->originStation->name}}
                    </a>

                    <p class="train-status text-muted mb-1">
                        <span>
                            @if (file_exists(public_path('img/' . $status->checkin->trip->category->value . '.svg')))
                                <img class="product-icon"
                                     src="{{ asset('img/' . $status->checkin->trip->category->value . '.svg') }}"
                                     alt="{{$status->checkin->trip->category->value}}"
                                />
                            @else
                                <i class="fa fa-train d-inline" aria-hidden="true"></i>
                            @endif
                            {{ $status->checkin->trip->linename }}
                            @if(isset($status->checkin->trip->journey_number) && !str_contains($status->checkin->trip->linename, $status->checkin->trip->journey_number))
                                <small>({{$status->checkin->trip->journey_number}})</small>
                            @endif
                        </span>
                        @if (Route::current()->uri == "status/{id}")
                            <span class="ps-2">
                                <i class="fa fa-route d-inline" aria-hidden="true"></i>
                                @if($status->checkin->distance < 1000)
                                    {{ $status->checkin->distance }}<small>m</small>
                                @else
                                    {{round($status->checkin->distance / 1000)}}<small>km</small>
                                @endif
                            </span>
                            <span class="ps-2">
                                <i class="fa fa-stopwatch d-inline" aria-hidden="true"></i>
                                {!! durationToSpan(secondsToDuration($status->checkin->duration * 60)) !!}
                            </span>
                            <br/>
                        @endif
                        <span class="">
                            <small>&rarr; {{ $status->checkin->trip->destinationStation->name }}</small>
                        </span>

                        @if($status->business !== Business::PRIVATE)
                            <!--
                            <span class="pl-sm-2">
                                <i class="fa {{$status->business->faIcon()}}"
                                   data-mdb-toggle="tooltip"
                                   data-mdb-placement="top"
                                   title="{{$status->business->title()}}"
                                   aria-hidden="true">
                                </i>
                                <span class="sr-only">{{$status->business->title()}}</span>
                            </span>
                            -->
                        @endif
                        @if($status->event != null)
                            <br/>
                            <span class="pl-sm-2">
                                <i class="fa fa-calendar-day" aria-hidden="true"></i>
                                <a href="{{ route('event', ['slug' => $status->event->slug]) }}">
                                    {{ $status->event->name }}
                                </a>
                            </span>
                        @endif
                    </p>

                    @php
                        $canCheckin = $status->canCheckin();
                    @endphp

                    <p class="train-status text-muted">
                        @if(!isset($status->travelChain))
                            <span class="badge badge-danger">Ohne Reisekette</span>
                        @elseif($status->planned)
                            <span class="badge badge-info">Geplant</span>
                        @elseif($status->planned === false)
                            <span class="badge badge-warning">Ungeplant</span>
                        @else
                            <span class="badge badge-danger">???</span>
                        @endif
                        @if($status->taken)
                            <span class="badge badge-success">Mitgefahren</span>
                        @elseif($status->taken === false)
                            <span class="badge badge-danger">Nicht mitgefahren</span>
                            <br/><span>Grund: </span><span>{{ $status->not_taken_reason?->getReason() ?? 'nicht angegeben' }}</span>
                        @elseif(!isset($status->travelChain))

                        @elseif($canCheckin)
                            <span class="badge badge-primary">Mitgefahren? &ndash; Angabe ausstehend</span>
                        @else
                            <span class="badge badge-light">Ausstehend</span>
                        @endif
                    </p>

                    @if($canCheckin)
                        <button class="btn btn-primary edit-taken mb-3" type="button"
                                data-trwl-status-id="{{ $status->id }}">
                            <i class="fas fa-train" aria-hidden="true"></i>&nbsp;
                            {{ __('modals.statusTaken-title') }}
                        </button>
                    @endif

                    @if($status->planned !== true && $status->taken === false)
                        <button class="btn btn-danger delete mb-3" type="button"
                                data-mdb-toggle="modal"
                                data-mdb-target="#modal-status-delete"
                                onclick="document.querySelector('#modal-status-delete input[name=\'statusId\']').value = '{{$status->id}}'; document.getElementById('delete-status-context').innerHTML = statusContextString(document.getElementById('status-{{ $status->id }}').dataset);">
                            <i class="fas fa-trash" aria-hidden="true"></i>
                            {{__('delete')}}
                        </button>
                    @endif

                    @if(!empty($status->body))
                        <p class="status-body"><i class="fas fa-quote-right" aria-hidden="true"></i>
                            {!! nl2br(e(preg_replace('~(\R{2})\R+~', '$1', $status->body))) !!}
                        </p>
                    @else
                        <p class="status-body text-muted"><i class="fas fa-edit" aria-hidden="true"></i>
                            <a href="#" class="edit" data-trwl-status-id="{{ $status->id }}"><em class="text-decoration-underline text-muted">{{__('status.no-notes-yet')}}</em></a>
                        </p>
                    @endif

                    @if($status->checkin->departure->isPast() && $status->checkin->arrival->isFuture())
                        <p class="text-muted font-italic">
                            {{ __('stationboard.next-stop') }}

                            @php
                                $nextStation = \App\Http\Controllers\Backend\Transport\StatusController::getNextStationForStatus($status);
                            @endphp
                            <a href="{{route('trains.stationboard', ['provider' => 'train', 'station' => $nextStation?->ibnr])}}"
                               class="text-trwl clearfix">
                                {{$nextStation?->name}}
                            </a>
                        </p>
                    @endif
                </li>
                <li>
                    <i class="trwl-bulletpoint" aria-hidden="true"></i>
                    <span class="text-trwl float-end">
                        @php($display_arrival = $status->checkin->displayArrival)
                        @isset($display_arrival->original)
                            <small style="text-decoration: line-through;" class="text-muted">
                                {{ userTime($display_arrival->original) }}
                            </small>
                            &nbsp;
                        @endisset
                        <span data-mdb-toggle="tooltip" title="{{$display_arrival->type->getTooltip()}}" @if($display_arrival->cancelled) class="text-muted" @endif>
                            {{ userTime($display_arrival->time) }}
                        </span>
                        @if($display_arrival->cancelled)
                            <span class="text-danger">Ausfall</span>
                        @endif
                    </span>
                    <a href="{{route('trains.stationboard', ['provider' => 'train', 'station' => $status->checkin->destinationStation->ibnr])}}"
                       class="text-trwl clearfix">
                        {{$status->checkin->destinationStation->name}}
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="progress">
        <div
            class="progress-bar progress-time {{ $status->event?->isPride ? 'progress-pride' : '' }}"
            role="progressbar"
            style="width: 0"
            data-valuenow="{{ time() }}"
            data-valuemin="{{ $status->checkin->displayDeparture->time->timestamp }}"
            data-valuemax="{{ $status->checkin->displayArrival->time->timestamp }}"
        ></div>
    </div>
    <div class="card-footer text-muted interaction px-3 px-md-4">
        <ul class="list-inline float-end">
            @can('like', $status)
                <li class="like-text list-inline-item me-0">
                    <a href="{{ auth()->user() ? '#' : route('login') }}"
                       class="like {{ auth()->user() && $status->likes->where('user_id', auth()->user()->id)->first() !== null ? 'fas fa-star' : 'far fa-star'}} {{ $status->user->id === 18574 ? 'peach' : '' }}"
                       data-trwl-status-id="{{ $status->id }}"></a>
                </li>
                <li class="like-text list-inline-item">
                        <span class="likeCount pl-1 @if($status->likes->count() == 0) d-none @endif">
                            {{ $status->likes->count() }}
                        </span>
                </li>
            @endcan
            <!--
            <li class="like-text list-inline-item">
                <i class="fas {{$status->visibility->faIcon()}} visibility-icon text-small"
                   aria-hidden="true" title="{{$status->visibility->title()}}"
                   data-mdb-toggle="tooltip"
                   data-mdb-placement="top"></i>
            </li>
            -->
            <li class="like-text list-inline-item">
                <div class="dropdown">
                    <a href="#" data-mdb-toggle="dropdown" aria-expanded="false">
                        &nbsp;
                        <i class="fa fa-edit" aria-hidden="true"></i>
                        &nbsp;
                    </a>
                    <ul class="dropdown-menu">
                        <!--
                        <li>
                            <button class="dropdown-item trwl-share"
                                    type="button"
                                    data-trwl-share-url="{{ route('status', ['id' => $status->id]) }}"
                                    @if(auth()->user() && $status->user_id == auth()->user()->id)
                                        data-trwl-share-text="{{ $status->socialText }}"
                                    @else
                                        data-trwl-share-text="{{ $status->description }}"
                                @endif
                            >
                                <div class="dropdown-icon-suspense">
                                    <i class="fas fa-share" aria-hidden="true"></i>
                                </div>
                                {{__('menu.share')}}
                            </button>
                        </li>
                        -->
                        @auth
                            @if(auth()->user()->id === $status->user_id)
                                <li>
                                    <button class="dropdown-item edit" type="button"
                                            data-trwl-status-id="{{ $status->id }}">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </div>
                                        {{__('edit')}}
                                    </button>
                                </li>
                                @if($canCheckin || isset($status->taken))
                                    <li>
                                        <button class="dropdown-item edit-taken" type="button"
                                                data-trwl-status-id="{{ $status->id }}">
                                            <div class="dropdown-icon-suspense">
                                                <i class="fas fa-train" aria-hidden="true"></i>
                                            </div>
                                            Mitgefahren?
                                        </button>
                                    </li>
                                @endif
                                <li>
                                    <button class="dropdown-item edit-destination" type="button"
                                            data-trwl-status-id="{{ $status->id }}">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-right-from-bracket" aria-hidden="true"></i>
                                        </div>
                                        {{ __('modals.editStatusDestination-title') }}
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item delete" type="button"
                                            data-mdb-toggle="modal"
                                            data-mdb-target="#modal-status-delete"
                                            onclick="document.querySelector('#modal-status-delete input[name=\'statusId\']').value = '{{$status->id}}'; document.getElementById('delete-status-context').innerHTML = statusContextString(document.getElementById('status-{{ $status->id }}').dataset);">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </div>
                                        {{__('delete')}}
                                    </button>
                                </li>
                            @else
                                <li>
                                    <button type="button" class="dropdown-item join"
                                            data-trwl-linename="{{$status->checkin->trip->linename}}"
                                            data-trwl-stop-name="{{$status->checkin->destinationStation->name}}"
                                            data-trwl-trip-id="{{$status->checkin->trip_id}}"
                                            data-trwl-destination="{{$status->checkin->destination}}"
                                            data-trwl-arrival="{{$status->checkin->arrival}}"
                                            data-trwl-start="{{$status->checkin->origin}}"
                                            data-trwl-departure="{{$status->checkin->departure}}"
                                            data-trwl-event-id="{{$status->event?->id}}"
                                    >
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                                        </div>
                                        {{__('status.join')}}
                                    </button>
                                </li>
                                <x-mute-button :user="$status->user" :dropdown="true"/>
                                <x-block-button :user="$status->user" :dropdown="true"/>
                            @endif
                            @admin
                            <li>
                                <hr class="dropdown-divider"/>
                            </li>
                            <li>
                                <a href="{{route('admin.status.edit', ['statusId' => $status->id])}}"
                                   class="dropdown-item">
                                    <div class="dropdown-icon-suspense">
                                        <i class="fas fa-tools" aria-hidden="true"></i>
                                    </div>
                                    Admin-Interface
                                </a>
                            </li>
                            @endadmin
                        @endauth
                    </ul>
                </div>
            </li>
        </ul>

        <ul class="list-inline">
            <!--
            <li id="avatar-small-{{ $status->id }}" class="d-lg-none list-inline-item">
                <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                    <img
                        src="{{ ProfilePictureController::getUrl($status->user) }}"
                        class="profile-image" alt="{{__('settings.picture')}}">
                </a>
            </li>
            -->
            <li class="list-inline-item">
                <!--
                <a href="{{ route('profile', ['username' => $status->user->username]) }}">
                    @if(auth()?->user()?->id == $status->user_id)
                        {{__('user.you')}}
                    @else
                        {{ $status->user->username }}
                    @endif
                </a>
                {{__('dates.-on-')}}
                -->
                <a href="{{ route('status', ['id' => $status->id]) }}" class="status-date">
                    {{ userTime($status->checkin->departure,'dd DD.MM.') }}
                </a>@if($status->travelChain !== null && Route::current()->uri != "travelchain/{id}") |
                    <a href="{{ route('travelchain', ['id' => $status->travelChain->id]) }}" class="status-chain fw-bold">
                        {{ $status->travelChain->title }}
                    </a>
                @endif
            </li>
        </ul>
    </div>
    @if(\Illuminate\Support\Facades\Gate::allows('like', $status) && Route::current()->uri == "status/{id}")
        @foreach($status->likes as $like)
            <div class="card-footer text-muted clearfix">
                <a href="{{ route('profile', ['username' => $like->user->username]) }}">
                    <img src="{{ ProfilePictureController::getUrl($like->user) }}"
                         class="profile-image float-start me-2" alt="{{__('settings.picture')}}">
                </a>
                <span class="like-text pl-2 d-table-cell">
                    <a href="{{ route('profile', ['username' => $like->user->username]) }}">
                        {{$like->user->username}}
                    </a>
                    @if($like->user->is($status->user))
                        {{ __('user.liked-own-status') }}
                    @else
                        {{ __('user.liked-status') }}
                    @endif
                </span>
            </div>
        @endforeach
    @endif
</div>
