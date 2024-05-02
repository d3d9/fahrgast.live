@php
    $chainHref = route('travelchain', ['id' => $chain->id]);
@endphp
<div class="card status chain mb-3" id="chain-{{ $chain->id }}"
     data-trwl-id="{{$chain->id}}"
     @if(auth()->check() && auth()->id() === $chain->user_id)
         data-trwl-chain-title="{{ $chain->title }}"
         data-trwl-chain-body="{{ $chain->body }}"
         data-trwl-business-id="{{ $chain->business?->value }}"
         data-fgl-reliability-importance="{{ $chain->reliability_importance?->value }}"
         data-fgl-planned-for-reliability="{{ $chain->planned_for_reliability?->value }}"
         data-fgl-finished="{{ $chain->finished?->value }}"
         data-fgl-felt-punctual="{{ $chain->felt_punctual?->value }}"
         data-fgl-felt-stressed="{{ $chain->felt_stressed?->value }}"
        @if(isset($destinationDistanceDelta))
         data-fgl-ddd="{{ $destinationDistanceDelta }}"
        @endif
    @endif
>
@if(!($suppressChainContent ?? false))
    <div class="card-body row">
        <div class="col">
            <h4>
                <a href="{{ $chainHref }}">
                    {{ $chain->title }}
                </a>
                @isset($chain->business)
                    &nbsp;<span class="badge badge-info" style="font-size: 0.4em; vertical-align: middle;">{{ $chain->business->title() }}</span>
                @endisset
            </h4>
            @php
                $totalCount = $chain->statuses_count;
                $plannedCount = $chain->planned_statuses_count;
                $takenCount = $chain->taken_statuses_count;
                $pendingCount = $chain->pending_statuses_count;
                $undefCount = $chain->undef_statuses_count;
            @endphp
            <p class="train-status">
                @include('includes.chain-badges')
            </p>
            <!--
            <p class="train-status text-muted">
                plan
                <span>
                    X Fahrten
                </span>
                <span class="ps-2">
                    <i class="fa fa-route d-inline" aria-hidden="true"></i>
                    {{round(0 / 1000)}}<small>km</small>
                </span>
                <span class="ps-2">
                    <i class="fa fa-stopwatch d-inline" aria-hidden="true"></i>
                    {!! durationToSpan(secondsToDuration(0 * 60)) !!}
                </span>
            </p>
            -->

            @php
            $endDataPending = $chain->endDataPending($totalCount, $pendingCount, $undefCount);
            @endphp
            @if($chain->dataPending())
                <button class="btn btn-primary edit-chain" type="button"
                        data-trwl-chain-id="{{ $chain->id }}">
                    <i class="fas fa-edit" aria-hidden="true"></i>&nbsp;
                    Fehlende Angaben ergänzen
                </button>
            @elseif($endDataPending)
                @php
                    $btext = isset($chain->finished) ? 'Daten zum Abschluss ergänzen' : 'Erfassung abschließen';
                @endphp
                @if(Route::is('travelchain'))
                    <button class="btn btn-primary finish-chain" type="button"
                            data-trwl-chain-id="{{ $chain->id }}">
                        <i class="fas fa-check-to-slot" aria-hidden="true"></i>&nbsp;
                        {{ $btext }}
                    </button>
                @else
                    <a class="btn btn-primary" href="{{ $chainHref }}">
                        <i class="fas fa-check-to-slot" aria-hidden="true"></i>&nbsp;
                        {{ $btext }}
                    </a>
                @endif
            @endif

            @if(!empty($chain->body))
                <p class="chain-body mb-0 mt-3"><i class="fas fa-quote-right" aria-hidden="true"></i>
                    {!! nl2br(e(preg_replace('~(\R{2})\R+~', '$1', $chain->body))) !!}
                </p>
            @endif
        </div>
    </div>
    <div class="card-footer text-muted interaction px-3 px-md-4">
        <ul class="list-inline float-end mb-0">
            <li class="list-inline-item">
                <div class="dropdown">
                    <a href="#" data-mdb-toggle="dropdown" aria-expanded="false">
                        &nbsp;
                        <i class="fa fa-edit" aria-hidden="true"></i>
                        &nbsp;
                    </a>
                    <ul class="dropdown-menu">
                        @auth
                            @if(auth()->user()->id === $chain->user_id)
                                <li>
                                    <button class="dropdown-item edit-chain" type="button"
                                            data-trwl-chain-id="{{ $chain->id }}">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </div>
                                        {{__('edit')}}
                                    </button>
                                </li>
                                @if($endDataPending || isset($chain->finished))
                                    <li>
                                        <button class="dropdown-item finish-chain" type="button"
                                                data-trwl-chain-id="{{ $chain->id }}">
                                            <div class="dropdown-icon-suspense">
                                                <i class="fas fa-check-to-slot" aria-hidden="true"></i>
                                            </div>
                                            {{ $endDataPending ? 'Erfassung abschließen' : 'Abschluss bearbeiten' }}
                                        </button>
                                    </li>
                                @endif
                                <!--
                                <li>
                                    <button class="dropdown-item delete-chain" type="button"
                                            data-mdb-toggle="modal"
                                            data-mdb-target="#modal-chain-delete"
                                            onclick="document.querySelector('#modal-chain-delete input[name=\'chainId\']').value = '{{$chain->id}}';">
                                        <div class="dropdown-icon-suspense">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </div>
                                        {{__('delete')}}
                                    </button>
                                </li>
                                -->
                            @endif
                            @admin
                            <li>
                                <hr class="dropdown-divider"/>
                            </li>
                            <li>
                                <a href="{{route('admin.chain.edit', ['chainId' => $chain->id])}}"
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

        <ul class="list-inline mb-0">
            <li class="list-inline-item">
                <a href="{{ $chainHref }}" class="status-date">
                    Angelegt am {{ userTime($chain->created_at,'dd ' . __('datetime-format')) }}
                </a>
            </li>
        </ul>
    </div>
@endif
</div>
