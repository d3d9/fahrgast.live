@php
    use Carbon\Carbon;
@endphp

@extends('layouts.app')

@section('title', 'RIS Journeys')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7" id="journey-planner">
                <div class="alert alert-info fs-6">
                    Hinweis: Diese Funktion sollte nur im Vorfeld zur Anlegung von Plan-Reiseketten verwendet werden. Echtzeitdaten wie Verspätungen oder Ausfälle werden hier nicht dargestellt.
                </div>

                @include('includes.journey-autocomplete')

                @if(!empty($journeys))
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-around align-items-center p-0">
                            <button type="button" class="btn btn-tertiary px-4 submitRelRef" data-mdb-ripple-init data-mdb-ripple-color="light" data-rel="earlier" data-ref="{{ $earlierRef }}">Früher</button>
                        </div>
                        <div class="card-body p-2">
                        <table class="table journeys table-sm mb-0">
                            <tbody>
                            @foreach($journeys as $refreshToken => $journeyData)
                                @php
                                    $journey = $journeyData["journey"];
                                    $journeyInfo = $journeyData["info"];
                                    extract($journeyInfo, EXTR_OVERWRITE | EXTR_REFS);
                                @endphp
                                <tr style="cursor: pointer;" onclick="window.location='#journey_{{ $refreshToken }}';">
                                    @if($hasTransport)
                                        <td>
                                            <!-- <i class="far fa-clock fa-sm"></i> -->
                                            {{ userTime($firstTransport->plannedDeparture) }}
                                            &ndash;
                                            {{ userTime($lastTransport->plannedArrival) }}
                                        </td>
                                        <td>
                                            @if($changes) <i class="fas fa-shuffle fa-sm"></i> {{ $changes }} @endif
                                        </td>
                                        <td>
                                        @foreach($journey->legs as $leg)
                                            @if($leg->walking ?? false)
                                                @if($loop->first || $loop->last)
                                                    <i class="fas fa-person-walking fa-sm"></i>
                                                @endif
                                            @else
                                                <strong class="fw-bold">{!! str_replace(' ', '&nbsp;', $leg->line->name) !!}</strong>&ensp;
                                            @endif
                                        @endforeach
                                        </td>
                                        <td>
                                            @if($journey->isAlternative ?? false)
                                                <i class="fas fa-triangle-exclamation fa-sm"></i>
                                            @endif
                                        </td>
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td><i class="fas fa-person-walking fa-sm"></i></td>
                                        <td></td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        </div>
                        <div class="card-footer d-flex justify-content-around align-items-center p-0">
                            <button type="button" class="btn btn-tertiary px-4 submitRelRef" data-mdb-ripple-init data-mdb-ripple-color="light" data-rel="later" data-ref="{{ $laterRef }}">Später</button>
                        </div>
                    </div>

                    @foreach($journeys as $refreshToken => $journeyData)
                        @php
                            $journey = $journeyData["journey"];
                            $journeyInfo = $journeyData["info"];
                            extract($journeyInfo, EXTR_OVERWRITE | EXTR_REFS);
                        @endphp
                        <div class="card journey mb-4" id="journey_{{$refreshToken}}">
                            <div class="card-header">
                                @if($hasTransport)
                                    <i class="far fa-clock fa-sm"></i>
                                    {{ userTime($firstTransport->plannedDeparture) }}
                                    &ndash;
                                    {{ userTime($lastTransport->plannedArrival) }},
                                    {{ $changes === 0 ? "direkt" : ($changes." Umstieg".($changes > 1?"e":"")) }}
                                    <br/>
                                    {{ $firstTransport->origin->name }}
                                    &rarr;
                                    {{ $lastTransport->destination->name }}
                                @else
                                    Fußweg
                                @endif
                            </div>
                            <div class="card-body">
                                @if($journey->isAlternative ?? false)
                                    <p class="text-muted">
                                    Achtung: Bei dieser Route handelt es sich um eine Alternativroute, die nur kurzfristig aufgrund einer Verspätung o. ä. möglich geworden ist. Häufig sollten solche Routen nicht als Plan-Reisekette angelegt werden, da sie normalerweise nicht in der aktuellen Zeitlage möglich sind.
                                    </p>
                                @endif
                                <ol class="list-group">
                                    <li class="list-group-item list-group-item-info">
                                        Start {{ userTime(reset($journey->legs)->plannedDeparture, __("datetime-format")) }}
                                    </li>
                                @foreach($journey->legs as $leg)
                                    <li class="list-group-item{{ ($leg->walking ?? false) ? " list-group-item-secondary" : " "}}">
                                        @if(($leg->walking ?? false) && !$loop->first && !$loop->last)
                                            Fußweg (Umstieg) {{ Carbon::parse($leg->plannedArrival)->diffInMinutes(Carbon::parse($leg->plannedDeparture)) }} min
                                        @else
                                            <div class="d-flex">
                                                <div class="">
                                                    {{ userTime($leg->plannedDeparture) }}
                                                </div>
                                                <div class="ms-2 me-auto">
                                                    {{ $leg->origin->name ?? $leg->origin->address }}
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="">
                                                    |
                                                </div>
                                                <div class="ms-2 me-auto">
                                                    @if($leg->walking ?? false)
                                                        Fußweg
                                                    @else
                                                        <strong class="fw-bold">{{ $leg->line->name }}</strong> <small class="text-muted">&rarr; {{ $leg->direction }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="">
                                                    {{ userTime($leg->plannedArrival) }}
                                                </div>
                                                <div class="ms-2 me-auto">
                                                    {{ $leg->destination->name ?? $leg->destination->address }}
                                                </div>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                                    <li class="list-group-item list-group-item-info">
                                        Ankunft {{ userTime(end($journey->legs)->plannedArrival, __("datetime-format")) }}
                                    </li>
                                </ol>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                @if($journey->isAlternative ?? false)
                                    Warnung: Alternativ-Route (s. o.)
                                @endif
                                <!-- FGLTODO was wenn nur fussweg? -->
                                <form action="{{ route('trains.journey-checkin') }}" method="post" style="width: 100%;">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $refreshToken }}" />
                                    <button class="btn {{ ($journey->isAlternative ?? false) ? "btn-secondary" : "btn-primary" }} float-end">Plan-Reisekette anlegen</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @elseif(!empty($error))
                    <div class="alert alert-warning text-center fs-6">
                        {{ $error }}
                    </div>
                @else
                    <div class="alert alert-warning text-center fs-4">
                        Keine Routen gefunden.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
