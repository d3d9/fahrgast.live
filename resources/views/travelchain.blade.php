@extends('layouts.app')

@php
    use App\Enum\TravelChainFinished;
    $title = $chain->title;
@endphp

@section('title', $title)
@section('canonical', route('travelchain', ['id' => $chain->id]))

@if($chain->user->prevent_index)
    @section('meta-robots', 'noindex')
@else
    @section('meta-description', $chain->description)
@endif

@section('head')
    @parent
    <meta property="og:title" content="{{ $title }}"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ url('/travelchain/'.$chain->id)  }}"/>
@endsection

@section('content')
    <div class="px-md-4 py-md-5 py-4 mt-n4 profile-banner mb-4">
        <div class="container">
            <div class="row">
                <div class="text-white col-md-6 px-md-4">
                    <h1 class="card-title h1-responsive font-bold mb-0 profile-name">
                        <strong>{{$title}}</strong>
                        @isset($chain->business)
                            &nbsp;<span class="badge badge-info" style="font-size: 0.4em; vertical-align: middle;">{{ $chain->business->title() }}</span>
                        @endisset
                    </h1>
                    <div style="display: none" aria-hidden="true">
                        @include('includes.chain', ['suppressChainContent' => true])
                    </div>
                    @php
                        $totalCount = count($statusHtml);
                        $plannedCount = $plannedStatuses->count();
                        $takenCount = $takenStatuses->count();
                        $pendingCount = $pendingStatuses->count();
                        $undefCount = $undefStatuses->count();
                        $endDataPending = $chain->endDataPending($totalCount, $pendingCount, $undefCount);
                    @endphp
                    <p class="train-status mb-0">
                        @include('includes.chain-badges')
                    </p>
                    <span class="d-flex flex-column flex-md-row justify-content-md-start align-items-md-center gap-md-2 gap-1 pt-1 pb-2 pb-md-0">
                        <!--<small class="font-weight-light profile-tag">&nbsp;</small>-->
                        <div class="d-flex flex-row justify-content-md-start align-items-md-center gap-2 mb-2 mt-2">
                            <button class="btn btn-sm btn-primary edit-chain" type="button" data-trwl-chain-id="{{ $chain->id }}">{{__('edit')}}</button>
                            @if($endDataPending || isset($chain->finished))
                                <button class="btn btn-sm btn-primary finish-chain" type="button" data-trwl-chain-id="{{ $chain->id }}">{{ isset($chain->finished) ? 'Abschluss bearbeiten' : 'Erfassung abschließen' }}</button>
                            @endif
                            <!-- <a href="#" class="btn btn-sm btn-primary disabled">Karte</a> -->
                            <button class="btn btn-sm btn-danger delete-chain" type="button" data-trwl-chain-id="{{ $chain->id }}"
                                data-mdb-toggle="modal"
                                data-mdb-target="#modal-chain-delete"
                                onclick="document.querySelector('#modal-chain-delete input[name=\'chainId\']').value = '{{$chain->id}}';"
                            >{{__('delete')}}</button>
                        </div>
                    </span>
                </div>
                <div class="text-white col-md-6 px-md-4">
                    @if(!empty($chain->body))
                        <p class="chain-body"><i class="fas fa-quote-right" aria-hidden="true"></i>
                            {!! nl2br(e(preg_replace('~(\R{2})\R+~', '$1', $chain->body))) !!}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="text-center text-white col-md-6 px-md-4 order-md-2 mt-3">
                    @php
                        // sort expected
                        $plannedDep = $plannedStatuses->depPlanned();
                        $plannedArr = $plannedStatuses->arrPlanned();
                        $plannedDur = $plannedArr?->diffInSeconds($plannedDep);

                        $takenDep = $takenStatuses->depReal();
                        $takenArr = $takenStatuses->arrReal();
                        $takenDur = $takenArr?->diffInSeconds($takenDep);
                    @endphp
                    Tatsächlich<br/>
                    <span class="profile-stats">
                            <span class="font-weight-bold"><i class="fa fa-train d-inline"></i>&nbsp;{{$takenStatuses->count()}}</span><span
                                class="small font-weight-lighter">&nbsp;{{__('stats.trips')}}</span>
                            <span class="font-weight-bold ps-sm-2"><i class="fa fa-route d-inline"></i>&nbsp;{{round($takenStatuses->distance() / 1000)}}</span><span
                                class="small font-weight-lighter">km</span>
                            <span class="font-weight-bold ps-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($takenStatuses->checkinDurationMins() * 60)) !!}</span>
                    </span>
                    <br/>
                    <span class="profile-stats">
                        <span class="font-weight-bold">{{ userTime($takenDep) }}</span>
                        &ndash; <span class="font-weight-bold">{{ userTime($takenArr) }}</span>
                        <span>({!! durationToSpan(secondsToDuration($takenDur)) !!})</span>
                        @if(isset($plannedArr) && isset($takenArr) && isset($chain->finished) && ($chain->finished->isArrived()))
                            <span class="ps-sm-2">({{ sprintf("%+d", $plannedArr->diffInSeconds($takenArr, false) / 60) }})</span>
                        @endif
                    </span>
                </div>
                <div class="text-center text-white col-md-6 px-md-4 order-md-1 mt-3">
                    Geplant<br/>
                    <span class="profile-stats">
                            <span class="font-weight-bold"><i class="fa fa-train d-inline"></i>&nbsp;{{$plannedStatuses->count()}}</span><span
                                class="small font-weight-lighter">&nbsp;{{__('stats.trips')}}</span>
                            <span class="font-weight-bold ps-sm-2"><i class="fa fa-route d-inline"></i>&nbsp;{{round($plannedStatuses->distance() / 1000)}}</span><span
                                class="small font-weight-lighter">km</span>
                            <span class="font-weight-bold ps-sm-2"><i class="fa fa-stopwatch d-inline"></i>&nbsp;{!! durationToSpan(secondsToDuration($plannedStatuses->checkinPlannedDurationMins() * 60)) !!}</span>
                    </span>
                    <br/>
                    <span class="profile-stats">
                        <span class="font-weight-bold">{{ userTime($plannedDep) }}</span>
                        &ndash; <span class="font-weight-bold">{{ userTime($plannedArr) }}</span>
                        <span>({!! durationToSpan(secondsToDuration($plannedDur)) !!})</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                @if($chain->dataPending())
                    <div class="alert alert-warning">
                        <h3>Es fehlen Angaben</h3>
                        <p>Bitte ergänzen Sie frühzeitig Angaben über diese Reisekette:</p>
                        <button class="btn btn-primary edit-chain" type="button"
                                data-trwl-chain-id="{{ $chain->id }}">
                            <i class="fas fa-edit" style="color: unset;" aria-hidden="true"></i>&nbsp;
                            Fehlende Angaben ergänzen
                        </button>
                    </div>
                @elseif($endDataPending)
                    <div class="alert alert-warning">
                        <h3>Am Ziel angekommen?</h3>
                        <p>{{__('travelchain.chainFinish-cta')}}</p>
                        <button class="btn btn-primary finish-chain" type="button"
                                data-trwl-chain-id="{{ $chain->id }}">
                            <i class="fas fa-edit" style="color: unset;" aria-hidden="true"></i>&nbsp;
                            {{ isset($chain->finished) ? 'Fehlende Daten ergänzen' : 'Erfassung abschließen' }}
                        </button>
                    </div>
                @endif
                @if(isset($ctaStatuses) && $ctaStatuses->count() >= 1)
                    <div class="alert alert-warning px-3">
                        <h3>
                            {{ __('travelchain.ctaStatuses-head') }}
                        </h3>
                        <p>
                            {{ __('travelchain.ctaStatuses-body') }}
                        </p>
                        <div class="statuses ctaStatuses">
                            @foreach($ctaStatuses as $status)
                                {!! $statusHtml[$status->id] !!}
                            @endforeach
                        </div>
                    </div>
                @endif
                @if(isset($undefStatuses) && $undefStatuses->count() >= 1)
                    <div class="alert alert-warning px-3">
                        <h3>
                            {{ __('travelchain.undefStatuses-head') }}
                        </h3>
                        <p>
                            {{ __('travelchain.undefStatuses-body') }}
                        </p>
                        <div class="statuses undefStatuses">
                            @foreach($undefStatuses as $status)
                                {!! $statusHtml[$status->id] !!}
                            @endforeach
                        </div>
                    </div>
                @endif
                @if(isset($pendingUnplannedStatuses) && $pendingUnplannedStatuses->count() >= 1)
                    <div class="alert alert-warning px-3">
                        <h3>
                            {{ __('travelchain.pendingStatuses-head') }}
                        </h3>
                        <p>
                            {{ __('travelchain.pendingStatuses-body') }}
                        </p>
                        <div class="statuses pendingStatuses">
                            @foreach($pendingUnplannedStatuses as $status)
                                {!! $statusHtml[$status->id] !!}
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 order-md-2 chain-real-statuses">
                <h3>Tatsächliche Reisekette</h3>
                @if($takenStatuses->count() === 0)
                    <div class="alert alert-warning text-center fs-4">
                        Keine Fahrten.
                    </div>
                @else
                    @foreach($takenStatuses as $status)
                        {!! $statusHtml[$status->id] !!}
                    @endforeach
                @endif
            </div>
            <div class="col-md-6 order-md-1 chain-planned-statuses">
                <h3>Geplante Reisekette</h3>
                @if($plannedStatuses->count() === 0)
                    <div class="alert alert-warning text-center fs-4">
                        Keine Fahrten.
                    </div>
                @else
                    @foreach($plannedStatuses as $status)
                        {!! $statusHtml[$status->id] !!}
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    @if(auth()->check() && auth()->user()->id == $chain->user_id)
        @include('includes.chain-modals')
        @include('includes.status-modals')
    @endif
@endsection
